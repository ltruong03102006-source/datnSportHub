<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'venue_id',
        'name',
        'category',      // Thêm mới
        'pricing_type',  // Thêm mới
        'description',
        'price',
        'stock',         // Thêm mới
        'unit',
        'image',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer', // Ép kiểu số nguyên
        'is_active' => 'boolean',
    ];

    // Một dịch vụ thuộc về một cơ sở
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    // Một dịch vụ có thể nằm trong nhiều đơn đặt sân
    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_services')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }
}