<?php

namespace App\Models;


use App\Models\VenueImage;
use Illuminate\Database\Eloquent\Builder;
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
        'province_code',
        'ward_code',
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

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_code', 'code');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_code', 'code');
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

    public function bookings(): HasManyThrough
    {
        return $this->hasManyThrough(Booking::class, Court::class);
    }

    // Active venues carrying their rating average and booking count for ranking
    public function scopeWithRankingStats(Builder $query): Builder
    {
        return $query
            ->with('sport')
            ->whereIn('status', ['active', 'approved'])
            ->whereHas('courts', fn (Builder $courts) => $courts->where('status', 'active'))
            ->withAvg(['reviews as avg_rating' => fn (Builder $q) => $q->where('reviews.is_hidden', false)], 'rating')
            ->withCount(['reviews as reviews_count' => fn (Builder $q) => $q->where('reviews.is_hidden', false)])
            ->withCount(['bookings as bookings_count' => fn (Builder $q) => $q->whereNotIn('bookings.status', ['cancelled', 'rejected'])]);
    }

    // Filter venues by administrative area; null codes are ignored (no-op)
    public function scopeFilterByLocation(Builder $query, ?string $provinceCode, ?string $wardCode = null): Builder
    {
        return $query
            ->when($provinceCode, fn (Builder $q) => $q->where('venues.province_code', $provinceCode))
            ->when($wardCode, fn (Builder $q) => $q->where('venues.ward_code', $wardCode));
    }

    // Average rating + starting price columns for the public listing cards
    public function scopeWithListingStats(Builder $query): Builder
    {
        return $query
            ->withAvg(['reviews as avg_rating' => fn (Builder $q) => $q->where('reviews.is_hidden', false)], 'rating')
            ->selectSub(function ($sub) {
                $sub->selectRaw('MIN(slot_prices.price)')
                    ->from('slot_prices')
                    ->join('time_slots', 'time_slots.id', '=', 'slot_prices.time_slot_id')
                    ->join('courts', 'courts.id', '=', 'time_slots.court_id')
                    ->whereColumn('courts.venue_id', 'venues.id');
            }, 'min_price');
    }

    // Keep venues that offer at least one slot price within [min, max]
    public function scopeFilterByPrice(Builder $query, ?int $min, ?int $max): Builder
    {
        if ($min === null && $max === null) {
            return $query;
        }

        return $query->whereExists(function ($sub) use ($min, $max) {
            $sub->selectRaw('1')
                ->from('slot_prices')
                ->join('time_slots', 'time_slots.id', '=', 'slot_prices.time_slot_id')
                ->join('courts', 'courts.id', '=', 'time_slots.court_id')
                ->whereColumn('courts.venue_id', 'venues.id')
                ->when($min !== null, fn ($q) => $q->where('slot_prices.price', '>=', $min))
                ->when($max !== null, fn ($q) => $q->where('slot_prices.price', '<=', $max));
        });
    }

    // Keep venues whose average (visible) review rating is at least $minRating
    public function scopeFilterByRating(Builder $query, ?float $minRating): Builder
    {
        return $query->when($minRating, function (Builder $q) use ($minRating) {
            $q->whereIn('venues.id', function ($sub) use ($minRating) {
                $sub->select('courts.venue_id')
                    ->from('reviews')
                    ->join('courts', 'courts.id', '=', 'reviews.court_id')
                    ->where('reviews.is_hidden', false)
                    ->groupBy('courts.venue_id')
                    ->havingRaw('AVG(reviews.rating) >= ?', [$minRating]);
            });
        });
    }

    // Add a distance_km column and keep venues within $radiusKm of (lat, lng)
    public function scopeFilterByDistance(Builder $query, ?float $lat, ?float $lng, ?float $radiusKm = null): Builder
    {
        if ($lat === null || $lng === null) {
            return $query;
        }

        // Haversine great-circle distance in kilometres
        $haversine = '6371 * acos(least(1, cos(radians(?)) * cos(radians(venues.lat)) '
            . '* cos(radians(venues.lng) - radians(?)) + sin(radians(?)) * sin(radians(venues.lat))))';

        $query->whereNotNull('venues.lat')
            ->whereNotNull('venues.lng')
            ->selectRaw("{$haversine} AS distance_km", [$lat, $lng, $lat])
            ->orderBy('distance_km');

        return $query->when($radiusKm, fn (Builder $q) => $q->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, $radiusKm]));
    }
}
