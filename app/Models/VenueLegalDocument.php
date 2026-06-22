<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueLegalDocument extends Model
{
    use HasFactory;

    protected $table = 'venue_legal_documents';

    protected $fillable = [
        'venue_id',
        'owner_name',
        'citizen_id',
        'business_license_number',
        'address',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'citizen_front_image',
        'citizen_back_image',
        'business_license_file',
        'rental_contract_file',
        'land_certificate_file',
        'status',
        'reject_reason',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
