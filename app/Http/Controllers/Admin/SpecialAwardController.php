<?php

namespace App\Http\Controllers\Admin;

use App\Services\SpecialAwardWinnerService;

use App\Http\Controllers\Controller;
use App\Models\Contestant;
use App\Models\Event;
use App\Models\SpecialAward;
use App\Models\SpecialAwardScore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SpecialAwardController extends Controller
{
    /**
     * Create a special award with criteria.
     */

    public function __construct(private SpecialAwardWinnerService $winnerService) {}

    public function store(
        Request $request,
        Event $event
    ): RedirectResponse {

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'award_type' => [
                'required',
                'in:special,minor,recognition',
            ],

            'allow_multiple_photos' => [
                'nullable',
                'boolean',
            ],

            'order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'criteria' => [
                'required',
                'array',
                'min:1',
            ],

            'criteria.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'criteria.*.weight' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'criteria.*.max_score' => [
                'required',
                'numeric',
                'min:1',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validate total criteria weight
        |--------------------------------------------------------------------------
        */

        $totalWeight = collect($data['criteria'])
            ->sum(function ($criterion) {
                return (float) $criterion['weight'];
            });

        if (abs($totalWeight - 100) > 0.01) {
            return back()
                ->withInput()
                ->withErrors([
                    'criteria' =>
                    'The criteria weights must total exactly 100%. '
                        . 'Current total: '
                        . number_format($totalWeight, 2)
                        . '%.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Create award + criteria
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $request,
            $event,
            $data
        ) {

            $award = $event->specialAwards()->create([
                'name' => $data['name'],

                'description' =>
                $data['description'] ?? null,

                'award_type' =>
                $data['award_type'],

                'allow_multiple_photos' =>
                $request->boolean(
                    'allow_multiple_photos'
                ),

                'order' =>
                $data['order'] ?? 0,
            ]);

            foreach ($data['criteria'] as $index => $criterion) {

                $award->criteria()->create([
                    'name' =>
                    $criterion['name'],

                    'weight' =>
                    $criterion['weight'],

                    'max_score' =>
                    $criterion['max_score'],

                    'order' =>
                    $index,
                ]);
            }
        });

        return back()->with(
            'status',
            'Special award and criteria created successfully.'
        );
    }


    /**
     * Update special award.
     */
    public function update(
        Request $request,
        Event $event,
        SpecialAward $award
    ): RedirectResponse {

        abort_unless(
            $award->event_id === $event->id,
            404
        );

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'award_type' => [
                'required',
                'in:special,minor,recognition',
            ],

            'allow_multiple_photos' => [
                'nullable',
                'boolean',
            ],

            'order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        $data['allow_multiple_photos'] =
            $request->boolean(
                'allow_multiple_photos'
            );

        $award->update($data);

        return back()->with(
            'status',
            'Special award updated successfully.'
        );
    }


    /**
     * Delete special award.
     */
    public function destroy(
        Event $event,
        SpecialAward $award
    ): RedirectResponse {

        abort_unless(
            $award->event_id === $event->id,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Delete stored photos
        |--------------------------------------------------------------------------
        */

        foreach ($award->photos as $photo) {
            if ($photo->photo_path) {
                Storage::disk('public')
                    ->delete($photo->photo_path);
            }
        }

        $award->delete();

        return back()->with(
            'status',
            'Special award deleted successfully.'
        );
    }


    /**
     * Select/change winner.
     */
    public function winner(
        Request $request,
        Event $event,
        SpecialAward $award
    ): RedirectResponse {

        abort_unless(
            $award->event_id === $event->id,
            404
        );

        $data = $request->validate([
            'winner_contestant_id' => [
                'required',
                'exists:contestants,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Make sure contestant belongs to this event
        |--------------------------------------------------------------------------
        */

        $contestant = Contestant::query()
            ->where('id', $data['winner_contestant_id'])
            ->where('event_id', $event->id)
            ->firstOrFail();

        $award->update([
            'winner_contestant_id' =>
            $contestant->id,
        ]);

        return back()->with(
            'status',
            "Winner selected for {$award->name}."
        );
    }


    /**
     * Upload photos for a contestant.
     */
    public function uploadPhotos(
        Request $request,
        Event $event,
        SpecialAward $award,
        Contestant $contestant
    ): RedirectResponse {

        abort_unless(
            $award->event_id === $event->id,
            404
        );

        abort_unless(
            $contestant->event_id === $event->id,
            404
        );

        if (!$award->allow_multiple_photos) {
            return back()->withErrors([
                'photos' =>
                'Multiple photos are not enabled for this award.',
            ]);
        }

        $request->validate([
            'photos' => [
                'required',
                'array',
                'min:1',
            ],

            'photos.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $currentCount = $award->photos()
            ->where('contestant_id', $contestant->id)
            ->count();

        foreach ($request->file('photos') as $index => $photo) {

            $path = $photo->store(
                'special-awards/' . $award->id,
                'public'
            );

            $award->photos()->create([
                'contestant_id' => $contestant->id,
                'photo_path' => $path,
                'order' => $currentCount + $index,
            ]);
        }

        return back()->with(
            'status',
            'Photos uploaded successfully.'
        );
    }


    /**
     * Delete an uploaded photo.
     */
    public function destroyPhoto(
        Event $event,
        SpecialAward $award,
        $photo
    ): RedirectResponse {

        abort_unless(
            $award->event_id === $event->id,
            404
        );

        $photo = $award->photos()
            ->where('id', $photo)
            ->firstOrFail();

        if ($photo->photo_path) {
            Storage::disk('public')
                ->delete($photo->photo_path);
        }

        $photo->delete();

        return back()->with(
            'status',
            'Photo deleted successfully.'
        );
    }

    protected function updateWinner(SpecialAward $award): void
    {
        $award->load('criteria');

        $criteria = $award->criteria;

        // No criteria = no automatic winner
        if ($criteria->isEmpty()) {
            $award->update([
                'winner_contestant_id' => null,
            ]);

            return;
        }

        // Get judges who have submitted scores
        $judgeIds = SpecialAwardScore::where('special_award_id', $award->id)
            ->distinct()
            ->pluck('judge_id');

        $contestants = Contestant::where('event_id', $award->event_id)
            ->where('is_active', true)
            ->get();

        $results = [];

        foreach ($contestants as $contestant) {

            $judgeTotals = [];

            foreach ($judgeIds as $judgeId) {

                $scores = SpecialAwardScore::where('special_award_id', $award->id)
                    ->where('contestant_id', $contestant->id)
                    ->where('judge_id', $judgeId)
                    ->get()
                    ->keyBy('criterion_id');

                // Judge must score ALL criteria
                if ($scores->count() < $criteria->count()) {
                    continue;
                }

                $judgeTotal = 0;

                foreach ($criteria as $criterion) {

                    $score = $scores->get($criterion->id);

                    if (!$score) {
                        continue 2;
                    }

                    $weightedScore =
                        ((float) $score->score / (float) $criterion->max_score)
                        * (float) $criterion->weight;

                    $judgeTotal += $weightedScore;
                }

                $judgeTotals[] = $judgeTotal;
            }

            // Only calculate contestant if at least one judge
            // has completed ALL criteria
            if (!empty($judgeTotals)) {
                $results[$contestant->id] =
                    array_sum($judgeTotals) / count($judgeTotals);
            }
        }

        // No completed scores yet
        if (empty($results)) {
            $award->update([
                'winner_contestant_id' => null,
            ]);

            return;
        }

        // Highest average score wins
        arsort($results);

        $winnerId = array_key_first($results);

        $award->update([
            'winner_contestant_id' => $winnerId,
        ]);
    }
    public function storeSpecialAwardScore(
        Request $request,
        Event $event,
        SpecialAward $award,
        Contestant $contestant
    ) {
        $this->authorizeJudge($event);

        abort_unless(
            $award->event_id === $event->id,
            404
        );

        abort_unless(
            $contestant->event_id === $event->id,
            404
        );

        if ($event->scoring_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Scoring is currently locked for this event.',
            ], 423);
        }

        $award->load('criteria');

        if ($award->criteria->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'This special award has no scoring criteria.',
            ], 422);
        }

        $request->validate([
            'scores' => ['required', 'array'],
            'scores.*' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        foreach ($award->criteria as $criterion) {

            $submittedScore = $request->input(
                'scores.' . $criterion->id
            );

            if ($submittedScore === null) {
                continue;
            }

            $score = min(
                (float) $submittedScore,
                (float) $criterion->max_score
            );

            SpecialAwardScore::updateOrCreate(
                [
                    'special_award_id' => $award->id,
                    'criterion_id' => $criterion->id,
                    'contestant_id' => $contestant->id,
                    'judge_id' => Auth::id(),
                ],
                [
                    'score' => $score,
                    'remarks' => $request->input('remarks'),
                ]
            );
        }


        return response()->json([
            'success' => true,
            'status' => 'saved',
            'message' => 'Special Award score saved successfully.',
            'redirect' => route(
                'judge.special-award.contestants',
                [
                    'event' => $event->id,
                    'award' => $award->id,
                ]
            ),
        ]);
    }

    public function recalculateWinner(SpecialAward $award)
    {
        $this->winnerService->recalculate($award); return back()->with('status', 'Winner recalculated.');
    }
}
