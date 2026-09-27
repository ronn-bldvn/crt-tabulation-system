<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Collection;

class TabulationService
{
    public function computeEventResults(Event $event): array
    {
        $event->loadMissing([
            'segments.criteria',
            'contestants' => fn($q) => $q->where('is_active', true)->orderedByNumber(),
            'specialAwards',
        ]);

        $segments = $event->segments;
        $contestants = $event->contestants;
        $specialAwardCount = $event->specialAwards->count();
        $specialAwardPoints = [];

        if ($specialAwardCount > 0) {
            $pointsPerAward = 2 / $specialAwardCount;
            foreach ($event->specialAwards as $award) {
                if ($award->winner_contestant_id !== null) {
                    $contestantId = $award->winner_contestant_id;
                    $specialAwardPoints[$contestantId] = ($specialAwardPoints[$contestantId] ?? 0)
                        + $pointsPerAward;
                }
            }
        }

        $scores = $event->scores()->get()->groupBy(['segment_id', 'contestant_id', 'judge_id']);

        $data = [];

        foreach ($contestants as $contestant) {
            $data[$contestant->id] = [
                'contestant' => $contestant,
                'segments' => [],
                'special_award_points' => round($specialAwardPoints[$contestant->id] ?? 0.0, 4),
                'points_score' => 0.0,
                'rank_average' => 0.0,
            ];
        }

        foreach ($segments as $segment) {
            $totalCriteriaWeight = (float) $segment->criteria->sum('weight');
            $totalCriteriaWeight = $totalCriteriaWeight > 0 ? $totalCriteriaWeight : 100;

            foreach ($contestants as $contestant) {
                $perJudge = [];

                $judgeGroups = $scores->get($segment->id, collect())->get($contestant->id, collect());

                $wonSpecialAwardsCount = $event->specialAwards
                    ->where('winner_contestant_id', $contestant->id)
                    ->count();
                $totalSpecialAwardsCount = $event->specialAwards->count();
                $specialAwardRatio = $totalSpecialAwardsCount > 0
                    ? $wonSpecialAwardsCount / $totalSpecialAwardsCount
                    : 0;

                if ($judgeGroups->isEmpty()) {
                    $hasAnyFillableCriterion = $segment->criteria->contains(
                        fn($c) => !$c->isSpecialAward()
                    );
                    if (!$hasAnyFillableCriterion && $segment->criteria->isNotEmpty()) {
                        $weightedSum = 0.0;
                        foreach ($segment->criteria as $criterion) {
                            if ($criterion->isSpecialAward() && (float) $criterion->max_score > 0) {
                                $weightedSum += $specialAwardRatio * (float) $criterion->weight;
                            }
                        }
                        if ($totalCriteriaWeight > 0) {
                            $perJudge[0] = round(($weightedSum / $totalCriteriaWeight) * 100, 4);
                        }
                    }
                }

                foreach ($judgeGroups as $judgeId => $judgeScores) {
                    $weightedSum = 0.0;

                    foreach ($segment->criteria as $criterion) {
                        if ((float) $criterion->max_score <= 0) {
                            continue;
                        }

                        if ($criterion->isSpecialAward()) {
                            $ratio = $specialAwardRatio;
                        } else {
                            $scoreRow = $judgeScores->firstWhere('criterion_id', $criterion->id);
                            if (! $scoreRow) {
                                continue;
                            }
                            $ratio = (float) $scoreRow->score / (float) $criterion->max_score;
                        }
                        $weightedSum += $ratio * (float) $criterion->weight;
                    }

                    $perJudge[$judgeId] = round(($weightedSum / $totalCriteriaWeight) * 100, 4);
                }

                $average = count($perJudge) > 0
                    ? round(array_sum($perJudge) / count($perJudge), 4)
                    : 0.0;

                $data[$contestant->id]['segments'][$segment->id] = [
                    'per_judge' => $perJudge,
                    'average' => $average,
                    'rank' => null,
                ];
            }

            $ranked = collect($data)
                ->sortByDesc(fn($row) => $row['segments'][$segment->id]['average'])
                ->values();

            $position = 0;
            $previousScore = null;
            foreach ($ranked as $index => $row) {
                $score = $row['segments'][$segment->id]['average'];
                if ($previousScore === null || $score < $previousScore) {
                    $position = $index + 1;
                }
                $data[$row['contestant']->id]['segments'][$segment->id]['rank'] = $position;
                $previousScore = $score;
            }
        }

        $totalSegmentWeight = (float) $segments->sum('weight');
        $totalSegmentWeight = $totalSegmentWeight > 0 ? $totalSegmentWeight : 100;

        foreach ($contestants as $contestant) {
            $weightedSum = 0.0;
            $rankWeightedSum = 0.0;

            foreach ($segments as $segment) {
                $seg = $data[$contestant->id]['segments'][$segment->id];
                $weightedSum += $seg['average'] * (float) $segment->weight;
                $rankWeightedSum += ($seg['rank'] ?? 0) * (float) $segment->weight;
            }

            $data[$contestant->id]['points_score'] = round(
                ($weightedSum / $totalSegmentWeight) + $data[$contestant->id]['special_award_points'],
                4
            );
            $data[$contestant->id]['rank_average'] = round($rankWeightedSum / $totalSegmentWeight, 4);
        }

        $rankingPoints = collect($data)
            ->sortByDesc('points_score')
            ->pluck('contestant.id')
            ->values()
            ->all();

        $rankingByRank = collect($data)
            ->sortBy('rank_average')
            ->pluck('contestant.id')
            ->values()
            ->all();

        return [
            'segments' => $segments,
            'contestants' => $data,
            'ranking_points' => $rankingPoints,
            'ranking_by_rank' => $rankingByRank,
        ];
    }

    public function finalRanking(Event $event): Collection
    {
        $results = $this->computeEventResults($event);
        $order = $event->ranking_method === 'rank' ? $results['ranking_by_rank'] : $results['ranking_points'];

        return collect($order)->values()->map(function ($contestantId, $index) use ($results) {
            $row = $results['contestants'][$contestantId];

            return [
                'place' => $index + 1,
                'contestant' => $row['contestant'],
                'points_score' => $row['points_score'],
                'special_award_points' => $row['special_award_points'],
                'rank_average' => $row['rank_average'],
                'segments' => $row['segments'],
            ];
        });
    }

    public function segmentCompleteness(Event $event, $segment): array
    {
        $judgeCount = $event->judges()->count();
        $contestantCount = $event->contestants()->where('is_active', true)->count();
        $criterionCount = $segment->criteria
            ->filter(fn($c) => !$c->isSpecialAward())
            ->count();

        $expected = $judgeCount * $contestantCount * $criterionCount;

        $actual = $segment->scores()
            ->whereHas('criterion', function ($q) {
                $q->whereRaw("LOWER(LTRIM(RTRIM(name))) != LOWER(LTRIM(RTRIM('special award')))");
            })
            ->count();

        return [
            'expected' => $expected,
            'actual' => $actual,
            'complete' => $expected > 0 && $actual >= $expected,
        ];
    }
}
