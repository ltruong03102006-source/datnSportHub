<?php

namespace App\Models;


use App\Models\VenueImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Venue extends Model
{
    use HasFactory;

    protected $table = 'venues';

    // Đã thay thế #[Fillable] bằng mảng chuẩn của Laravel
    protected $fillable = [
        'owner_id', 
        'sport_id', 
        'name', 
        'address', 
        'lat', 
        'lng', 
        'description', 
        'rules',
        'banner', 
        'status',
        'phone',
        'email',
        'open_hours',
        'close_hours',
        'google_maps_address'
    ];

    // Ép kiểu (Casts) tọa độ sang số thực để tránh lỗi hiển thị bản đồ
    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

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

    public function logs(): HasMany
    {
        return $this->hasMany(VenueLog::class);
    }

    public function getOwnerPhoneAttribute(): ?string
    {
        return $this->ownerRegistration?->phone;
    }
    public function images()
    {
        return $this->hasMany(VenueImage::class);
    }

    public function legalDocument()
    {
        return $this->hasOne(VenueLegalDocument::class);
    }

    /** Người dùng đã thêm cơ sở này vào danh sách yêu thích. */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'venue_id', 'user_id')
            ->withTimestamps();
    }

    public function isFavoritedBy(User $user): bool
    {
        return $this->favoritedBy()->whereKey($user->getKey())->exists();
    }

    public function cancellationPolicies()
    {
        // Tự động sắp xếp tăng dần để thuật toán check từ mốc sát giờ nhất
        return $this->hasMany(CancellationPolicy::class)->orderBy('hours_before', 'asc');
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Court::class);
    }
}
