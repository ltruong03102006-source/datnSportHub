<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['owner_id', 'sport_id', 'name', 'address', 'lat', 'lng', 'description', 'banner', 'status'])]
class Venue extends Model
{
    use HasFactory;

    protected $table = 'venues';

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function courts(): HasMany
    {
        return $this->hasMany(Court::class);
    }

    public function ownerRegistration(): BelongsTo
    {
        return $this->belongsTo(OwnerRegistration::class, 'owner_id', 'user_id');
    }

    public function getOwnerPhoneAttribute(): ?string
    {
        return $this->ownerRegistration?->phone;
    }
}
