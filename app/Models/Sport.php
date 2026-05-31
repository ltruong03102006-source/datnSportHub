<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'icon', 'slug'])]
class Sport extends Model
{
    use HasFactory;

    protected $table = 'sports';

    public function venues(): HasMany
    {
        return $this->hasMany(Venue::class);
    }
}
