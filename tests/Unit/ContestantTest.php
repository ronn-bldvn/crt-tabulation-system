<?php

namespace Tests\Unit;

use App\Models\Contestant;
use PHPUnit\Framework\TestCase;

class ContestantTest extends TestCase
{
    public function test_it_derives_candidate_gender_from_number_prefix(): void
    {
        $this->assertSame('Female', (new Contestant(['number' => 'FC1']))->candidateGender());
        $this->assertSame('Female', (new Contestant(['number' => 'fc2']))->candidateGender());
        $this->assertSame('Male', (new Contestant(['number' => 'MC1']))->candidateGender());
        $this->assertSame('Male', (new Contestant(['number' => 'mc2']))->candidateGender());
        $this->assertNull((new Contestant(['number' => 'C3']))->candidateGender());
    }
}
