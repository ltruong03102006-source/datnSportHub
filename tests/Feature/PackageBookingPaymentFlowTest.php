<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenuePackage;
use App\Services\PackageBookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PackageBookingPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_booking_starts_as_pending_and_requires_payment(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);
        $user = User::factory()->create();

        $sport = Sport::create([
            'name' => 'Badminton',
            'slug' => 'badminton',
        ]);

        $venue = Venue::create([
            'owner_id' => $owner->id,
            'sport_id' => $sport->id,
            'name' => 'Venue Package',
            'address' => 'Address Package',
            'status' => 'active',
            'allow_package_booking' => true,
        ]);

        $court = Court::create([
            'venue_id' => $venue->id,
            'name' => 'Court 1',
            'status' => 'active',
            'is_bookable_online' => true,
        ]);

        $startDate = Carbon::today()->toDateString();
        $weekday = Carbon::today()->dayOfWeek;
        $timeSlotId = DB::table('time_slots')->insertGetId([
            'court_id' => $court->id,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'duration_minutes' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $secondTimeSlotId = DB::table('time_slots')->insertGetId([
            'court_id' => $court->id,
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'duration_minutes' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('slot_prices')->insert([
            [
                'time_slot_id' => $timeSlotId,
                'price' => 100000,
                'price_type' => 'normal',
                'day_of_week' => $weekday,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'time_slot_id' => $secondTimeSlotId,
                'price' => 150000,
                'price_type' => 'normal',
                'day_of_week' => $weekday,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $package = VenuePackage::create([
            'venue_id' => $venue->id,
            'name' => 'Gói tuần',
            'type' => 'week',
            'duration' => 1,
            'discount_percent' => 10,
            'status' => 'active',
        ]);

        $service = app(PackageBookingService::class);
        $bookingPackage = $service->createPendingPackage($user->id, $package, [[
            'court_id' => $court->id,
            'time_slot_ids' => [$timeSlotId, $secondTimeSlotId],
            'weekday' => $weekday,
        ]], $startDate);

        $this->assertSame('pending_payment', $bookingPackage->status);
        $this->assertSame(250000.0, (float) $bookingPackage->total_amount);
        $this->assertSame(225000.0, (float) $bookingPackage->final_amount);
        $this->assertCount(0, $bookingPackage->bookings);
        $this->assertSame('pending', $bookingPackage->transactions->first()->payment_status);

        $activatedPackage = $service->activateAfterPayment($bookingPackage, $user->id);
        $booking = $activatedPackage->bookings->first();

        $this->assertSame('active', $activatedPackage->status);
        $this->assertCount(1, $activatedPackage->bookings);
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('10:00:00', $booking->start_time);
        $this->assertSame('12:00:00', $booking->end_time);
        $this->assertSame(250000.0, (float) $booking->total_price);
    }
}
