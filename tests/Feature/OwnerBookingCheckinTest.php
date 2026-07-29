<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\Court;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerBookingCheckinTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_check_in_their_today_booking(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 7, 29, 9, 0, 0, 'Asia/Ho_Chi_Minh'));

        [$owner, $booking] = $this->createOwnerBooking();

        $response = $this->actingAs($owner)
            ->post(route('owner.web.checkins.check-in', $booking), [
                'checkin_note' => 'Khach den dung gio',
            ]);

        $response->assertRedirect();

        $booking->refresh();
        $this->assertNotNull($booking->checked_in_at);
        $this->assertSame($owner->id, $booking->checked_in_by);
        $this->assertSame('Khach den dung gio', $booking->checkin_note);
        $this->assertSame(1, BookingLog::where('booking_id', $booking->id)->count());

        Carbon::setTestNow();
    }

    public function test_owner_cannot_check_in_another_owners_booking(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 7, 29, 9, 0, 0, 'Asia/Ho_Chi_Minh'));

        [, $booking] = $this->createOwnerBooking();
        $anotherOwner = User::factory()->create(['role' => 'owner', 'status' => 'active']);

        $response = $this->actingAs($anotherOwner)
            ->post(route('owner.web.checkins.check-in', $booking));

        $response->assertForbidden();
        $this->assertNull($booking->fresh()->checked_in_at);

        Carbon::setTestNow();
    }

    public function test_no_show_booking_cannot_be_checked_in(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 7, 29, 9, 0, 0, 'Asia/Ho_Chi_Minh'));

        [$owner, $booking] = $this->createOwnerBooking([
            'no_show_at' => now('Asia/Ho_Chi_Minh'),
            'no_show_by' => null,
        ]);

        $response = $this->actingAs($owner)
            ->from(route('owner.web.checkins.index'))
            ->post(route('owner.web.checkins.check-in', $booking));

        $response->assertRedirect(route('owner.web.checkins.index'));
        $response->assertSessionHas('error');
        $this->assertNull($booking->fresh()->checked_in_at);

        Carbon::setTestNow();
    }

    private function createOwnerBooking(array $bookingOverrides = []): array
    {
        $owner = User::factory()->create(['role' => 'owner', 'status' => 'active']);
        $customer = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $sport = Sport::create(['name' => 'Badminton', 'slug' => 'badminton-'.uniqid()]);
        $venue = Venue::create([
            'owner_id' => $owner->id,
            'sport_id' => $sport->id,
            'name' => 'Checkin Venue',
            'address' => 'Ha Noi',
            'status' => 'active',
        ]);
        $court = Court::create([
            'venue_id' => $venue->id,
            'name' => 'Court 1',
            'status' => 'active',
            'is_bookable_online' => true,
        ]);

        $booking = Booking::create(array_merge([
            'court_id' => $court->id,
            'user_id' => $customer->id,
            'slot_date' => Carbon::today('Asia/Ho_Chi_Minh')->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'total_price' => 100000,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
        ], $bookingOverrides));

        return [$owner, $booking];
    }
}
