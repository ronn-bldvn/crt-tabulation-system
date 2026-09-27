<?php

namespace App\Models;

use App\Models\SpecialAward;
use App\Models\SpecialAwardPhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contestant extends Model
{
    protected $fillable = [
        'event_id',
        'number',
        'name',
        'representing',
        'photo_path',
        'bio',
        'is_active',
    ];

    use HasFactory;

    public function candidateGender(): ?string
    {
        $number = strtoupper(trim((string) $this->number));

        if (str_starts_with($number, 'FC')) {
            return 'Female';
        }

        if (str_starts_with($number, 'MC')) {
            return 'Male';
        }

        return null;
    }

    public function scopeOrderedByNumber($query)
    {
        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'mysql') {
            return $query
                ->orderByRaw("REGEXP_SUBSTR(`number`, '^[^0-9]+') COLLATE utf8mb4_unicode_ci ASC")
                ->orderByRaw("CAST(REGEXP_SUBSTR(`number`, '[0-9]+') AS UNSIGNED) ASC")
                ->orderBy('number', 'ASC');
        }

        if ($driver === 'sqlite') {
            return $query
                ->orderByRaw("SUBSTR(`number`, 0, LENGTH(`number`) - LENGTH(LTRIM(`number`, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz')) + 1) ASC")
                ->orderByRaw("CAST(SUBSTR(`number`, LENGTH(`number`) - LENGTH(LTRIM(`number`, '0123456789')) + 1) AS INTEGER) ASC")
                ->orderBy('number', 'ASC');
        }

        if ($driver === 'pgsql') {
            return $query
                ->orderByRaw("SUBSTRING(`number` FROM '^[^0-9]+') ASC")
                ->orderByRaw("CAST(SUBSTRING(`number` FROM '[0-9]+') AS INTEGER) ASC")
                ->orderBy('number', 'ASC');
        }

        return $query->orderBy('number', 'ASC');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function specialAwards()
    {
        return $this->hasMany(
            SpecialAward::class,
            'winner_contestant_id'
        );
    }

    public function specialAwardPhotos()
    {
        return $this->hasMany(
            SpecialAwardPhoto::class
        );
    }
}
