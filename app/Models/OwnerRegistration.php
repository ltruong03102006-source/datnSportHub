<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'phone', 'email', 'status', 'rejection_reason'])]
class OwnerRegistration extends Model
{
    use HasFactory;

    protected $table = 'owner_registrations';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
