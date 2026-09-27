<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Segment extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'order',
        'weight',
        'is_locked',
    ];

    use HasFactory;

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'is_locked' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(Criterion::class)->orderBy('order');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function totalCriteriaWeight(): float
    {
        return (float) $this->criteria()->sum('weight');
    }
}
