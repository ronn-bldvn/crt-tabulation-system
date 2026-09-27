<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contestant;
use App\Models\Event;
use App\Models\User;
use App\Services\TabulationService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\SpecialAwardWinnerService;


class TabulationController extends Controller
{
    public function __construct(
        protected TabulationService $tabulation,
        protected SpecialAwardWinnerService $specialAwardWinner,
    ) {}



    public function show(Event $event)
    {
        $results = $this->tabulation->computeEventResults($event);
        $ranking = $this->tabulation->finalRanking($event);

        $completeness = $event->segments->mapWithKeys(
            fn($segment) => [$segment->id => $this->tabulation->segmentCompleteness($event, $segment)]
        );

        return view('admin.tabulation.show', compact('event', 'results', 'ranking', 'completeness'));
    }

    public function printResults(Event $event)
    {
        $ranking = $this->tabulation->finalRanking($event);

        $pdf = Pdf::loadView('print.results', compact('event', 'ranking'))->setPaper('a4', 'portrait');

        return $pdf->stream("results-{$event->slug}.pdf");
    }

    public function printBreakdown(Event $event)
    {
        $results = $this->tabulation->computeEventResults($event);

        $event->loadMissing([
            'specialAwards' => fn ($q) => $q->orderBy('order'),
            'specialAwards.winner',
        ]);

        $pdf = Pdf::loadView('print.breakdown', compact('event', 'results'))->setPaper('a4', 'landscape');

        return $pdf->stream("breakdown-{$event->slug}.pdf");
    }

    public function printScoresheet(Event $event, User $judge)
    {
        abort_unless($judge->role === 'judge', 404);

        $event->load([
            'segments.criteria',
            'specialAwards',
            'contestants' => fn($q) => $q->where('is_active', true)->orderedByNumber(),
        ]);
        $scores = $event->scores()->where('judge_id', $judge->id)->get();

        $pdf = Pdf::loadView('print.scoresheet', compact('event', 'judge', 'scores'))->setPaper('a4', 'portrait');

        return $pdf->stream("scoresheet-{$judge->name}-{$event->slug}.pdf");
    }

    public function printCertificate(Event $event, Contestant $contestant)
    {
        $ranking = $this->tabulation->finalRanking($event);
        $placement = collect($ranking)->firstWhere('contestant.id', $contestant->id);

        $pdf = Pdf::loadView('print.certificate', compact('event', 'contestant', 'placement'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream("certificate-{$contestant->name}.pdf");
    }

    public function printSpecialAwards(Event $event)
    {
        $event->load(['specialAwards' => fn($q) => $q->orderBy('order'), 'specialAwards.criteria']);

        $rankings = $event->specialAwards->mapWithKeys(
            fn($award) => [$award->id => $this->specialAwardWinner->rankContestants($award)]
        );

        $pdf = Pdf::loadView('print.special-award', compact('event', 'rankings'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("special-awards-{$event->slug}.pdf");
    }

public function printSpecialAwardsBreakdown(Event $event)
{
    $event->load(['specialAwards' => fn ($q) => $q->orderBy('order'), 'specialAwards.criteria']);

    $breakdowns = $event->specialAwards->mapWithKeys(
        fn ($award) => [$award->id => $this->specialAwardWinner->judgeBreakdown($award)]
    );

    $rankings = $event->specialAwards->mapWithKeys(
        fn ($award) => [$award->id => $this->specialAwardWinner->rankContestants($award)]
    );

    $pdf = Pdf::loadView('print.special-awards-breakdown', compact('event', 'breakdowns', 'rankings'))
        ->setPaper('a4', 'landscape');

    return $pdf->stream("special-awards-breakdown-{$event->slug}.pdf");
}
}
