<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'division_id',
        'name',
        'slug',
        'logo_path',
        'contact_name',
        'contact_email',
        'contact_phone',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $club): void {
            if (blank($club->slug)) {
                $club->slug = Str::slug($club->name);
            }
        });
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function correctionLinks(): HasMany
    {
        return $this->hasMany(CorrectionLink::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
