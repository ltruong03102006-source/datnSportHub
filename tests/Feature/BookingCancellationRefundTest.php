<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookingCancellationRefundTest extends TestCase
{
    use DatabaseTransactions;

    public function test_customer_cancel_booking_refunds_wallet_when_payment_status_is_success(): void
    {
        Queue::fake();

        $owner = User::factory()->create(['role' => 'owner', 'status' => 'active']);
        $customer = User::factory()->create();
        
        $wallet = $customer->getOrCreateWallet();
        $wallet->update(['balance' => 50000]);

        $sport = Sport::create(['name' => 'Badminton Cancellation Test', 'slug' => 'badminton-cancel-test']);
        $venue = Venue::create([
            'owner_id' => $owner->id,
            'sport_id' => $sport->id,
            'name' => 'Venue Refund Test',
            'address' => '123 Test St',
            'status' => 'active',
        ]);
        $court = Court::create([
            'venue_id' => $venue->id,
            'name' => 'Court Refund Test',
            'status' => 'active',
            'is_bookable_online' => true,
        ]);

        $slotDate = Carbon::tomorrow()->toDateString();

        $booking = Booking::create([
            'court_id' => $court->id,
            'user_id' => $customer->id,
            'slot_date' => $slotDate,
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'total_price' => 200000,
            'status' => 'confirmed',
            'payment_status' => 'success', // Online payment stored as success
            'payment_method' => 'vnpay',
            'created_at' => now(),
        ]);

        $this->actingAs($customer);

        $response = $this->postJson("/account/bookings/{$booking->id}/cancel", [
            'reason' => 'Bận đột xuất',
        ]);

        $response->assertOk();

        $wallet->refresh();
        $booking->refresh();

        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('refunded', $booking->refund_status);
        $this->assertGreaterThan(50000, (float) $wallet->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'booking_id' => $booking->id,
            'type' => 'refund',
        ]);
    }

    public function test_owner_api_cancel_paid_booking_refunds_customer_wallet(): void
    {
        Queue::fake();

        $owner = User::factory()->create(['role' => 'owner', 'status' => 'active']);
        $customer = User::factory()->create();

        $wallet = $customer->getOrCreateWallet();
        $wallet->update(['balance' => 100000]);

        $sport = Sport::create(['name' => 'Tennis API Test', 'slug' => 'tennis-api-test']);
        $venue = Venue::create([
            'owner_id' => $owner->id,
            'sport_id' => $sport->id,
            'name' => 'Venue Owner API Test',
            'address' => '456 Owner St',
            'status' => 'active',
        ]);
        $court = Court::create([
            'venue_id' => $venue->id,
            'name' => 'Court Owner API Test',
            'status' => 'active',
            'is_bookable_online' => true,
        ]);

        $booking = Booking::create([
            'court_id' => $court->id,
            'user_id' => $customer->id,
            'slot_date' => Carbon::tomorrow()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'total_price' => 150000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'wallet',
            'created_at' => now(),
        ]);

        $this->actingAs($owner, 'sanctum');

        $response = $this->postJson("/api/owner/bookings/{$booking->id}/cancel", [
            'reason' => 'Sân bảo trì đột xuất',
        ]);

        $response->assertOk();

        $wallet->refresh();
        $booking->refresh();

        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('refunded', $booking->refund_status);
        $this->assertEquals(250000, (float) $wallet->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'booking_id' => $booking->id,
            'type' => 'refund',
            'amount' => 150000,
        ]);
    }
}
