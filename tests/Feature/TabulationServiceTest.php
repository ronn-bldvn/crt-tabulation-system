<?php

namespace Tests\Feature;

use App\Models\Contestant;
use App\Models\Event;
use App\Models\SpecialAward;
use App\Services\TabulationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TabulationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_special_award_points_are_divided_equally_and_added_to_the_score(): void
    {
        $event = Event::create([
            'name' => 'Test Event',
            'slug' => 'test-event',
        ]);
        $firstContestant = Contestant::create([
            'event_id' => $event->id,
            'number' => '1',
            'name' => 'First Contestant',
        ]);
        $secondContestant = Contestant::create([
            'event_id' => $event->id,
            'number' => '2',
            'name' => 'Second Contestant',
        ]);

        foreach (range(1, 5) as $index) {
            SpecialAward::create([
                'event_id' => $event->id,
                'name' => "Award {$index}",
                'winner_contestant_id' => $index <= 2
                    ? $firstContestant->id
                    : $secondContestant->id,
            ]);
        }

        $results = app(TabulationService::class)->computeEventResults($event);

        $this->assertSame(0.8, $results['contestants'][$firstContestant->id]['special_award_points']);
        $this->assertSame(1.2, $results['contestants'][$secondContestant->id]['special_award_points']);
        $this->assertSame(0.8, $results['contestants'][$firstContestant->id]['points_score']);
        $this->assertSame(1.2, $results['contestants'][$secondContestant->id]['points_score']);
    }
}