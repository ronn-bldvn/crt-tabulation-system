<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialAward extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'description',
        'award_type',
        'allow_multiple_photos',
        'order',
        'winner_contestant_id',
    ];

    protected $casts = [
        'allow_multiple_photos' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function winner()
    {
        return $this->belongsTo(
            Contestant::class,
            'winner_contestant_id'
        );
    }

    public function criteria()
    {
        return $this->hasMany(
            SpecialAwardCriterion::class
        )->orderBy('order');
    }

    public function photos()
    {
        return $this->hasMany(
            SpecialAwardPhoto::class
        )->orderBy('order');
    }

    public function scores()
    {
        return $this->hasMany(
            SpecialAwardScore::class
        );
    }
}