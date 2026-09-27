<?php

namespace Tests\Unit;

use App\Models\Criterion;
use PHPUnit\Framework\TestCase;

class CriterionTest extends TestCase
{
    public function test_it_recognizes_special_award_criteria_with_suffixes(): void
    {
        foreach (
            [
                'Special Award',
                'SPECIAL AWARD',
                'Special Awards',
                'Special Award Points',
                'Special   Award Points',
            ] as $name
        ) {
            $criterion = new Criterion(['name' => $name]);

            $this->assertTrue($criterion->isSpecialAward(), $name);
        }
    }

    public function test_it_does_not_classify_unrelated_criteria_as_special_awards(): void
    {
        $criterion = new Criterion(['name' => 'Best in Talent']);

        $this->assertFalse($criterion->isSpecialAward());
    }
}
