<?php

namespace App\Models;

use App\Models\SpecialAward;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'event_date',
        'status',
        'ranking_method',
        'scoring_locked',
    ];

    use HasFactory;

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'scoring_locked' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name).'-'.Str::random(4);
            }
        });
    }

    public function segments(): HasMany
    {
        return $this->hasMany(Segment::class)->orderBy('order');
    }

    public function contestants(): HasMany
    {
        return $this->hasMany(Contestant::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function judges(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_judge', 'event_id', 'judge_id')
            ->withPivot(['access_code'])
            ->withTimestamps();
    }

    public function totalSegmentWeight(): float
    {
        return (float) $this->segments()->sum('weight');
    }

    public function specialAwards()
    {
        return $this->hasMany(SpecialAward::class)
            ->orderBy('order');
    }
}
