<?php

namespace App\Services;

use App\Models\Venue;
use Illuminate\Support\Collection;

class VenueRankingService
{
    private const RATING_WEIGHT = 0.7;
    private const BOOKING_WEIGHT = 0.3;
    private const MAX_RATING = 5;

    /**
     * Build the three ranking lists used on the "featured venues" page.
     *
     * @return array{featured: Collection, most_booked: Collection, top_rated: Collection}
     */
    public function getRankings(int $limit = 10): array
    {
        $venues = $this->scoredVenues();

        return [
            'featured' => $venues->sortByDesc('ranking_score')->take($limit)->map(fn ($v) => $this->format($v))->values(),
            'most_booked' => $venues->sortByDesc('bookings_count')->take($limit)->map(fn ($v) => $this->format($v))->values(),
            'top_rated' => $venues->where('reviews_count', '>', 0)->sortByDesc('avg_rating')->take($limit)->map(fn ($v) => $this->format($v))->values(),
        ];
    }

    private function scoredVenues(): Collection
    {
        $venues = Venue::withRankingStats()->get();

        // Normalise booking counts against the busiest venue so the 70/30 weights are comparable
        $maxBookings = max(1, (int) $venues->max('bookings_count'));

        return $venues->each(function (Venue $venue) use ($maxBookings): void {
            $ratingNorm = (float) $venue->avg_rating / self::MAX_RATING;
            $bookingNorm = (int) $venue->bookings_count / $maxBookings;

            $score = ($ratingNorm * self::RATING_WEIGHT) + ($bookingNorm * self::BOOKING_WEIGHT);
            $venue->ranking_score = round($score * 100, 1);
        });
    }

    private function format(Venue $venue): array
    {
        return [
            'venue_id' => $venue->id,
            'name' => $venue->name,
            'sport_name' => $venue->sport?->name,
            'address' => $venue->address,
            'thumbnail' => $venue->banner ? asset('storage/' . $venue->banner) : null,
            'avg_rating' => round((float) $venue->avg_rating, 1),
            'reviews_count' => (int) $venue->reviews_count,
            'bookings_count' => (int) $venue->bookings_count,
            'ranking_score' => (float) ($venue->ranking_score ?? 0),
        ];
    }
}
