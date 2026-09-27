<?php

namespace App\Http\Controllers\Judge;

use App\Services\SpecialAwardWinnerService;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Score;
use App\Models\Segment;
use App\Models\Contestant;
use App\Models\SpecialAward;
use App\Models\SpecialAwardScore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScoringController extends Controller
{
    public function events()
    {
        $events = Auth::user()->judgingEvents()->latest()->get();

        return view('judge.events', compact('events'));
    }

    public function __construct(private SpecialAwardWinnerService $winnerService) {}


    public function show(Event $event)
    {
        $this->authorizeJudge($event);

        $event->load([
            'segments' => fn($q) => $q->orderBy('order'),

            'contestants' => fn($q) => $q
                ->where('is_active', true)
                ->orderedByNumber(),

            'specialAwards' => fn($q) => $q
                ->orderBy('order'),

            'specialAwards.criteria',
        ]);

        $myScoreCounts = Score::where('judge_id', Auth::id())
            ->where('event_id', $event->id)
            ->whereHas('criterion', function ($q) {
                $q->whereRaw("LOWER(LTRIM(RTRIM(name))) != LOWER(LTRIM(RTRIM('special award')))");
            })
            ->selectRaw('segment_id, contestant_id, count(*) as cnt')
            ->groupBy('segment_id', 'contestant_id')
            ->get();

        return view('judge.event', compact('event', 'myScoreCounts'));
    }

public function score(
    Event $event,
    Segment $segment,
    \App\Models\Contestant $contestant
) {
    $this->authorizeJudge($event);

    abort_unless(
        $segment->event_id === $event->id &&
            $contestant->event_id === $event->id,
        404
    );

    $event->loadMissing(['specialAwards']);

    $segment->load([
        'criteria' => fn($query) => $query->orderBy('order')
    ]);

    $existing = Score::where('judge_id', Auth::id())
        ->where('event_id', $event->id)
        ->where('segment_id', $segment->id)
        ->where('contestant_id', $contestant->id)
        ->get()
        ->keyBy('criterion_id');

    $totalSpecialAwards = $event->specialAwards->count();
    $wonSpecialAwards = $event->specialAwards
        ->where('winner_contestant_id', $contestant->id)
        ->count();

    $specialAwardCriterionScore = 0;
    if ($totalSpecialAwards > 0) {
        $specialAwardCriterionScore = ($wonSpecialAwards / $totalSpecialAwards);
    }

    // Get all active contestants for Previous/Next navigation
    $contestants = $event->contestants()
        ->where('is_active', true)
        ->orderedByNumber()
        ->get();

    $currentIndex = $contestants->search(
        fn($c) => $c->id === $contestant->id
    );

    $next = $currentIndex !== false
        ? $contestants->get($currentIndex + 1)
        : null;

    $prev = $currentIndex !== false && $currentIndex > 0
        ? $contestants->get($currentIndex - 1)
        : null;

    return view('judge.score', compact(
        'event',
        'segment',
        'contestant',
        'existing',
        'next',
        'prev',
        'currentIndex',
        'contestants',
        'totalSpecialAwards',
        'wonSpecialAwards',
        'specialAwardCriterionScore'
    ));
}

    public function store(
        Request $request,
        Event $event,
        Segment $segment,
        \App\Models\Contestant $contestant
    ) {
        $this->authorizeJudge($event);

        abort_unless(
            $segment->event_id === $event->id &&
                $contestant->event_id === $event->id,
            404
        );

        abort_if(
            $event->scoring_locked || $segment->is_locked,
            423,
            'Scoring is currently locked for this segment.'
        );

        $fillableCriteriaCount = $segment->criteria
            ->filter(fn($c) => !$c->isSpecialAward())
            ->count();

        $data = $request->validate([
            'scores' => $fillableCriteriaCount > 0 ? ['required', 'array'] : ['nullable', 'array'],
            'scores.*' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        if (isset($data['scores'])) {
            foreach ($data['scores'] as $criterionId => $value) {

                $criterion = $segment->criteria()->find($criterionId);

                if (!$criterion || $criterion->isSpecialAward()) {
                    continue;
                }

                Score::updateOrCreate(
                    [
                        'criterion_id' => $criterion->id,
                        'contestant_id' => $contestant->id,
                        'judge_id' => Auth::id(),
                    ],
                    [
                        'event_id' => $event->id,
                        'segment_id' => $segment->id,
                        'score' => min(
                            (float) $value,
                            (float) $criterion->max_score
                        ),
                        'remarks' => $data['remarks'] ?? null,
                    ]
                );
            }
        }

        /*
    |--------------------------------------------------------------------------
    | Save & Next
    |--------------------------------------------------------------------------
    |
    | Your JavaScript sends:
    | Accept: application/json
    |
    | So return JSON when the request expects JSON.
    |
    */
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'saved',
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Normal Save Score
    |--------------------------------------------------------------------------
    |
    | The normal form submission should return to the contestants list.
    |
    */
        return redirect()
            ->route('judge.contestants', [
                'event' => $event->id,
                'segment' => $segment->id,
            ])
            ->with('success', 'Score saved successfully.');
    }



    protected function authorizeJudge(Event $event): void
    {
        abort_unless(
            $event->judges()->where('judge_id', Auth::id())->exists(),
            403,
            'You are not assigned to this event.'
        );
    }

    public function segments(Event $event)
    {
        $this->authorizeJudge($event);

        $event->load([
            'segments.criteria',
            'contestants' => function ($query) {
                $query->where('is_active', true)
                    ->orderedByNumber();
            },
        ]);

        $segmentStatus = [];

        foreach ($event->segments as $segment) {
            $fillableCriteriaCount = $segment->criteria
                ->filter(fn($c) => !$c->isSpecialAward())
                ->count();

            $segmentStatus[$segment->id] = [
                'complete' => false,
                'actual' => 0,
                'expected' => $fillableCriteriaCount,
            ];
        }

        return view('judge.segments', compact(
            'event',
            'segmentStatus'
        ));
    }

    public function contestants(Event $event, Segment $segment)
    {
        $this->authorizeJudge($event);

        abort_unless(
            $segment->event_id === $event->id,
            404
        );

        $event->load([
            'contestants' => function ($query) {
                $query->where('is_active', true)
                    ->orderedByNumber();
            },
        ]);

        $contestants = $event->contestants;

        $judgeId = Auth::id();

        $scoreStatus = [];

        $fillableCriteriaCount = $segment->criteria
            ->filter(fn($c) => !$c->isSpecialAward())
            ->count();

        foreach ($contestants as $contestant) {

            $expected = $fillableCriteriaCount;

            $actual = Score::where('judge_id', $judgeId)
                ->where('event_id', $event->id)
                ->where('segment_id', $segment->id)
                ->where('contestant_id', $contestant->id)
                ->whereHas('criterion', function ($q) {
                    $q->whereRaw("LOWER(LTRIM(RTRIM(name))) NOT LIKE 'special award%'");
                })
                ->count();

            $scoreStatus[$contestant->id] = [
                'complete' => $expected > 0 && $actual >= $expected,
                'actual' => $actual,
                'expected' => $expected,
            ];
        }

        return view('judge.contestants', compact(
            'event',
            'segment',
            'contestants',
            'scoreStatus'
        ));
    }
    public function specialAwards(Event $event)
    {
        $this->authorizeJudge($event);

        $event->load([
            'specialAwards' => function ($query) {
                $query->orderBy('order');
            },
            'specialAwards.criteria',
            'specialAwards.winner',
        ]);

        $judgeId = Auth::id();

        $awardStatus = [];

        foreach ($event->specialAwards as $award) {

            $contestantCount = $event->contestants()
                ->where('is_active', true)
                ->count();

            $completed = SpecialAwardScore::where(
                'special_award_id',
                $award->id
            )
                ->where('judge_id', $judgeId)
                ->distinct('contestant_id')
                ->count('contestant_id');

            $expected = $contestantCount;

            $awardStatus[$award->id] = [
                'actual' => $completed,
                'expected' => $expected,
                'complete' => $expected > 0 && $completed >= $expected,
            ];
        }

        return view(
            'judge.special-awards',
            compact('event', 'awardStatus')
        );
    }

    public function specialAwardContestants(
        Event $event,
        SpecialAward $award
    ) {
        $this->authorizeJudge($event);

        abort_unless(
            $award->event_id === $event->id,
            404
        );

        $award->load('criteria');

        $contestants = $event->contestants()
            ->where('is_active', true)
            ->orderedByNumber()
            ->get();


        $scoreStatus = [];

        foreach ($contestants as $contestant) {

            $expected = $award->criteria->count();

            $actual = SpecialAwardScore::where(
                'special_award_id',
                $award->id
            )
                ->where('judge_id', Auth::id())
                ->where('contestant_id', $contestant->id)
                ->count();

            $scoreStatus[$contestant->id] = [
                'actual' => $actual,
                'expected' => $expected,
                'complete' => $expected > 0 && $actual >= $expected,
            ];
        }

        return view(
            'judge.special-award-contestants',
            compact(
                'event',
                'award',
                'contestants',
                'scoreStatus'
            )
        );
    }


    public function specialAwardScore(
        Event $event,
        SpecialAward $award,
        Contestant $contestant
    ) {
        $this->authorizeJudge($event);

        abort_unless(
            $award->event_id === $event->id &&
                $contestant->event_id === $event->id,
            404
        );

        $award->load([
            'criteria' => function ($query) {
                $query->orderBy('order');
            },
            'photos' => function ($query) use ($contestant) {
                $query->where('contestant_id', $contestant->id)
                    ->orderBy('order');
            },
        ]);

        $existing = SpecialAwardScore::where(
            'special_award_id',
            $award->id
        )
            ->where('judge_id', Auth::id())
            ->where('contestant_id', $contestant->id)
            ->get()
            ->keyBy('criterion_id');

        // Previous / Next contestants
        $contestants = $event->contestants()
            ->where('is_active', true)
            ->orderedByNumber()
            ->get();


        $currentIndex = $contestants->search(
            fn($c) => $c->id === $contestant->id
        );

        $next = $currentIndex !== false
            ? $contestants->get($currentIndex + 1)
            : null;

        $prev = $currentIndex !== false && $currentIndex > 0
            ? $contestants->get($currentIndex - 1)
            : null;

        return view(
            'judge.special-award-score',
            compact(
                'event',
                'award',
                'contestant',
                'existing',
                'next',
                'prev',
                'currentIndex',
                'contestants'
            )
        );
    }

    public function storeSpecialAwardScore(
        Request $request,
        Event $event,
        SpecialAward $award,
        Contestant $contestant
    ) {
        $this->authorizeJudge($event);

        abort_unless(
            $award->event_id === $event->id &&
                $contestant->event_id === $event->id,
            404
        );

        abort_if(
            $event->scoring_locked,
            423,
            'Scoring is currently locked for this event.'
        );

        $data = $request->validate([
            'scores' => ['required', 'array'],
            'scores.*' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        foreach ($data['scores'] as $criterionId => $value) {

            $criterion = $award->criteria()
                ->where('id', $criterionId)
                ->first();

            if (!$criterion) {
                continue;
            }

            SpecialAwardScore::updateOrCreate(
                [
                    'special_award_id' => $award->id,
                    'criterion_id' => $criterion->id,
                    'contestant_id' => $contestant->id,
                    'judge_id' => Auth::id(),
                ],
                [
                    'score' => min(
                        (float) $value,
                        (float) $criterion->max_score
                    ),
                    'remarks' => $data['remarks'] ?? null,
                ]
            );
        }

        $this->winnerService->recalculate($award);

        \Log::info('recalculating winner', ['award' => $award->id]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => 'saved',
                'message' => 'Special Award score saved successfully.',
                'redirect' => route('judge.special-award.contestants', [
                    'event' => $event->id,
                    'award' => $award->id,
                ]),
            ]);
        }

        return redirect()
            ->route('judge.special-awards', [
                'event' => $event->id,
            ])
            ->with('success', 'Special Award score saved successfully.');
    }


    protected function updateSpecialAwardWinner(SpecialAward $award): void
    {
        $award->loadMissing('criteria');

        $criteria = $award->criteria;

        if ($criteria->isEmpty()) {
            $award->update([
                'winner_contestant_id' => null,
            ]);

            return;
        }

        $contestants = Contestant::where('event_id', $award->event_id)
            ->where('is_active', true)
            ->get();

        $judgeIds = SpecialAwardScore::where('special_award_id', $award->id)
            ->pluck('judge_id')
            ->unique();

        if ($judgeIds->isEmpty()) {
            $award->update([
                'winner_contestant_id' => null,
            ]);

            return;
        }

        $averages = [];

        foreach ($contestants as $contestant) {

            $judgeTotals = [];

            foreach ($judgeIds as $judgeId) {

                $scores = SpecialAwardScore::where('special_award_id', $award->id)
                    ->where('contestant_id', $contestant->id)
                    ->where('judge_id', $judgeId)
                    ->get()
                    ->keyBy('criterion_id');

                /*
             * A judge must score ALL criteria
             * before this contestant is included.
             */
                if ($scores->count() < $criteria->count()) {
                    continue;
                }

                $total = 0;

                foreach ($criteria as $criterion) {

                    $score = $scores->get($criterion->id);

                    if (!$score) {
                        continue 2;
                    }

                    $weightedScore =
                        ((float) $score->score / (float) $criterion->max_score)
                        * (float) $criterion->weight;

                    $total += $weightedScore;
                }

                $judgeTotals[] = $total;
            }

            /*
         * Calculate average of the completed judges.
         */
            if (!empty($judgeTotals)) {

                $averages[$contestant->id] =
                    array_sum($judgeTotals) / count($judgeTotals);
            }
        }

        /*
     * Nobody has completed scoring yet.
     */
        if (empty($averages)) {

            $award->update([
                'winner_contestant_id' => null,
            ]);

            return;
        }

        /*
     * Highest average wins.
     */
        arsort($averages);

        $winnerId = array_key_first($averages);

        $award->update([
            'winner_contestant_id' => $winnerId,
        ]);
    }
}
