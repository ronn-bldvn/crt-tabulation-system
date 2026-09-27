<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Criterion extends Model
{
    protected $fillable = [
        'segment_id',
        'name',
        'description',
        'order',
        'weight',
        'max_score',
    ];

    use HasFactory;

    protected $table = 'criteria';

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'max_score' => 'decimal:2',
        ];
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function isSpecialAward(): bool
    {
        $name = preg_replace('/\s+/', ' ', trim((string) $this->name));

        return preg_match('/^special awards?(?:\b|$)/i', $name) === 1;
    }
}
