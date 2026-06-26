<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['name', 'icon', 'slug'])]
class Sport extends Model
{
    use HasFactory;

    protected $table = 'sports';

    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class);
    }

    public function courts(): HasManyThrough
    {
        return $this->hasManyThrough(Court::class, Venue::class);
    }

    public function scopeWithActiveCourtsCount(Builder $query): Builder
    {
        return $query->withCount(['courts as courts_count' => function (Builder $query) {
            $query->where('courts.status', 'active')
                ->whereIn('venues.status', ['active', 'approved']);
        }]);
    }
}
