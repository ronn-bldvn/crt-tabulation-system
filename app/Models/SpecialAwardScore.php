<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialAwardScore extends Model
{
    protected $fillable = [
        'special_award_id',
        'criterion_id',
        'contestant_id',
        'judge_id',
        'score',
        'remarks',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function award()
    {
        return $this->belongsTo(
            SpecialAward::class,
            'special_award_id'
        );
    }

    public function criterion()
    {
        return $this->belongsTo(
            SpecialAwardCriterion::class,
            'criterion_id'
        );
    }

    public function contestant()
    {
        return $this->belongsTo(
            Contestant::class,
            'contestant_id'
        );
    }

    public function judge()
    {
        return $this->belongsTo(
            User::class,
            'judge_id'
        );
    }
}