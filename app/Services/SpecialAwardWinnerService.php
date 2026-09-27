<?php

namespace App\Services;

use App\Models\Contestant;
use App\Models\SpecialAward;
use App\Models\SpecialAwardScore;
use App\Models\User;
use Illuminate\Support\Collection;

class SpecialAwardWinnerService
{
    /**
     * Recalculate and persist the winner for a special award,
     * based on all currently submitted judge scores.
     */
    public function recalculate(SpecialAward $award): void
    {
        $results = $this->computeAverages($award);

        if ($results->isEmpty()) {
            $award->update(['winner_contestant_id' => null]);
            return;
        }

        $winnerId = $results->keys()->first();

        $award->update([
            'winner_contestant_id' => (int) $winnerId,
        ]);
    }

    /**
     * Full ranking for a special award: every contestant who has at
     * least one judge with all criteria scored, ordered highest first.
     *
     * Returns a collection of:
     *   ['rank' => int, 'contestant' => Contestant, 'score' => float]
     */
    public function rankContestants(SpecialAward $award): Collection
    {
        $averages = $this->computeAverages($award);

        if ($averages->isEmpty()) {
            return collect();
        }

        $contestants = Contestant::whereIn('id', $averages->keys())
            ->get()
            ->keyBy('id');

        $rank = 0;
        $lastScore = null;
        $position = 0;

        return $averages->map(function ($score, $contestantId) use (
            $contestants, &$rank, &$lastScore, &$position
        ) {
            $position++;

            // Standard competition ranking: ties share a rank (1,1,3...)
            if ($lastScore === null || round($score, 4) !== round($lastScore, 4)) {
                $rank = $position;
            }
            $lastScore = $score;

            return [
                'rank' => $rank,
                'contestant' => $contestants->get($contestantId),
                'score' => $score,
            ];
        })->values();
    }

    /**
     * Raw per-judge, per-criterion breakdown for a special award.
     *
     * Returns a collection keyed by judge_id, each entry:
     *   [
     *     'judge' => User,
     *     'contestants' => Collection keyed by contestant_id, each:
     *       [
     *         'contestant' => Contestant,
     *         'scores'     => [criterion_id => float],   // raw scores given
     *         'total'      => float|null,                 // weighted total, null if incomplete
     *         'complete'   => bool,                        // scored every criterion?
     *       ]
     *   ]
     *
     * Unlike computeAverages(), this includes every judge and every
     * contestant that has at least one score row, complete or not,
     * so gaps in scoring are visible on the printout.
     */
    public function judgeBreakdown(SpecialAward $award): Collection
    {
        $award->load('criteria');
        $criteria = $award->criteria;

        if ($criteria->isEmpty()) {
            return collect();
        }

        $allScores = SpecialAwardScore::where('special_award_id', $award->id)->get();

        if ($allScores->isEmpty()) {
            return collect();
        }

        $judges = User::whereIn('id', $allScores->pluck('judge_id')->unique())
            ->get()
            ->keyBy('id');

        $contestants = Contestant::whereIn('id', $allScores->pluck('contestant_id')->unique())
            ->get()
            ->keyBy('id');

        $breakdown = collect();

        foreach ($allScores->groupBy('judge_id') as $judgeId => $judgeScores) {

            $contestantRows = collect();

            foreach ($judgeScores->groupBy('contestant_id') as $contestantId => $scores) {

                $byCriterion = $scores->keyBy('criterion_id');

                $rawScores = [];
                $complete = true;
                $total = 0.0;

                foreach ($criteria as $criterion) {
                    $score = $byCriterion->get($criterion->id);

                    if (! $score) {
                        $complete = false;
                        $rawScores[$criterion->id] = null;
                        continue;
                    }

                    $rawScores[$criterion->id] = (float) $score->score;

                    $max = (float) $criterion->max_score;
                    if ($max > 0) {
                        $total += ((float) $score->score / $max) * (float) $criterion->weight;
                    }
                }

                $contestantRows->put($contestantId, [
                    'contestant' => $contestants->get($contestantId),
                    'scores' => $rawScores,
                    'total' => $complete ? $total : null,
                    'complete' => $complete,
                ]);
            }

            // Order rows by contestant number for a stable printout
            $contestantRows = $contestantRows->sortBy(
                fn ($row) => $row['contestant']->number ?? 0
            );

            $breakdown->put($judgeId, [
                'judge' => $judges->get($judgeId),
                'contestants' => $contestantRows,
            ]);
        }

        // Order judges by name for a stable printout
        return $breakdown->sortBy(fn ($row) => $row['judge']->name ?? '');
    }

    /**
     * contestant_id => weighted average score, sorted highest first.
     * Only includes contestants with at least one judge who scored
     * every criterion for this award.
     */
    protected function computeAverages(SpecialAward $award): Collection
    {
        $award->load('criteria');
        $criteria = $award->criteria;

        if ($criteria->isEmpty()) {
            return collect();
        }

        $allScores = SpecialAwardScore::where('special_award_id', $award->id)->get();

        if ($allScores->isEmpty()) {
            return collect();
        }

        $results = [];

        foreach ($allScores->groupBy('contestant_id') as $contestantId => $contestantScores) {
            $judgeTotals = [];

            foreach ($contestantScores->groupBy('judge_id') as $judgeScores) {
                $byCriterion = $judgeScores->keyBy('criterion_id');

                // Judge must have scored every current criterion to count
                $hasAll = $criteria->every(fn ($c) => $byCriterion->has($c->id));
                if (! $hasAll) {
                    continue;
                }

                $judgeTotal = 0.0;
                foreach ($criteria as $criterion) {
                    $max = (float) $criterion->max_score;
                    if ($max <= 0) {
                        continue;
                    }
                    $judgeTotal += ((float) $byCriterion[$criterion->id]->score / $max)
                        * (float) $criterion->weight;
                }

                $judgeTotals[] = $judgeTotal;
            }

            if (! empty($judgeTotals)) {
                $results[$contestantId] = array_sum($judgeTotals) / count($judgeTotals);
            }
        }

        if (empty($results)) {
            return collect();
        }

        arsort($results);

        return collect($results);
    }
}