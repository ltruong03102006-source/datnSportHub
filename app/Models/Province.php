<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    protected $table = 'provinces';

    public $timestamps = false;

    protected $fillable = ['code', 'name'];

    public function wards(): HasMany
    {
        return $this->hasMany(Ward::class, 'province_code', 'code');
    }

    public function scopeOrderedByName(Builder $query): Builder
    {
        return $query->orderBy('name');
    }
}
