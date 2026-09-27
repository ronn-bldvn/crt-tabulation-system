<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialAwardPhoto extends Model
{
    protected $fillable = [
        'special_award_id',
        'contestant_id',
        'photo_path',
        'order',
    ];

    public function award()
    {
        return $this->belongsTo(
            SpecialAward::class,
            'special_award_id'
        );
    }

    public function contestant()
    {
        return $this->belongsTo(
            Contestant::class
        );
    }
}