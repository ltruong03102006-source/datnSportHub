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
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_is_auto_confirmed_and_logged(): void
    {
        Queue::fake();

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
            'name' => 'Venue A',
            'address' => 'Address 1',
            'status' => 'active',
        ]);

        $court = Court::create([
            'venue_id' => $venue->id,
            'name' => 'Court 1',
            'status' => 'active',
            'is_bookable_online' => true,
        ]);

        $slotDate = Carbon::tomorrow()->toDateString();
        $timeSlot = \Illuminate\Support\Facades\DB::table('time_slots')->insertGetId([
            'court_id' => $court->id,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'duration_minutes' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('slot_prices')->insert([
            'time_slot_id' => $timeSlot,
            'price' => 100000,
            'price_type' => 'normal',
            'day_of_week' => Carbon::parse($slotDate)->dayOfWeek,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/bookings', [
            'court_id' => $court->id,
            'slot_date' => $slotDate,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'note' => 'Test booking',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.status', 'confirmed');
        $response->assertJsonPath('data.payment_status', 'unpaid');
        $response->assertJsonPath('data.booking_id', 1);
        $response->assertJsonPath('data.id', 1);
        $response->assertJsonFragment([
            'message' => 'Booking confirmed successfully',
        ]);

        $booking = Booking::first();
        $this->assertNotNull($booking);
        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('unpaid', $booking->payment_status);
        $this->assertSame(1, BookingLog::where('booking_id', $booking->id)->count());
        $this->assertSame('confirmed', BookingLog::where('booking_id', $booking->id)->first()->new_status);
    }

    public function test_conflicting_booking_returns_conflict_status(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);
        $user = User::factory()->create();

        $sport = Sport::create([
            'name' => 'Tennis',
            'slug' => 'tennis',
        ]);

        $venue = Venue::create([
            'owner_id' => $owner->id,
            'sport_id' => $sport->id,
            'name' => 'Venue B',
            'address' => 'Address 2',
            'status' => 'active',
        ]);

        $court = Court::create([
            'venue_id' => $venue->id,
            'name' => 'Court 2',
            'status' => 'active',
            'is_bookable_online' => true,
        ]);

        $slotDate = Carbon::tomorrow()->toDateString();
        $timeSlot = \Illuminate\Support\Facades\DB::table('time_slots')->insertGetId([
            'court_id' => $court->id,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'duration_minutes' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('slot_prices')->insert([
            'time_slot_id' => $timeSlot,
            'price' => 100000,
            'price_type' => 'normal',
            'day_of_week' => Carbon::parse($slotDate)->dayOfWeek,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Booking::create([
            'court_id' => $court->id,
            'user_id' => $user->id,
            'slot_date' => $slotDate,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'total_price' => 100000,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/bookings', [
            'court_id' => $court->id,
            'slot_date' => $slotDate,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $response->assertStatus(409);
        $response->assertJsonFragment([
            'message' => 'This time slot has already been booked',
        ]);
        $this->assertSame(1, Booking::where('court_id', $court->id)->count());
    }
}
