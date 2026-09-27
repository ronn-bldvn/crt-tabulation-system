<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialAwardCriterion extends Model
{
    protected $fillable = [
        'special_award_id',
        'name',
        'weight',
        'max_score',
        'order',
    ];

    protected $casts = [
        'weight' => 'float',
        'max_score' => 'float',
    ];

    public function award()
    {
        return $this->belongsTo(
            SpecialAward::class,
            'special_award_id'
        );
    }

    public function scores()
    {
        return $this->hasMany(
            SpecialAwardScore::class
        );
    }
}