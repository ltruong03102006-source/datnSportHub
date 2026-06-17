<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Venue;
use App\Models\Court;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DummyBookingSeeder extends Seeder
{
    public function run()
    {
        // Find owneruser1
        $owner = User::where('email', 'owneruser1@example.com')->first();
        if (!$owner) {
            echo "Owner not found!\n";
            return;
        }

        // Get a user to act as the customer
        $customer = User::where('email', 'test@example.com')->first();
        if (!$customer) {
            $customer = User::factory()->create(['email' => 'test_customer@example.com']);
        }

        $venues = $owner->venues;
        if ($venues->isEmpty()) {
            echo "Owner has no venues. Cannot create dummy bookings.\n";
            return;
        }

        echo "Generating dummy bookings for owner: {$owner->name}...\n";

        $statuses = ['completed', 'completed', 'completed', 'confirmed', 'pending', 'cancelled'];
        
        $count = 0;
        foreach ($venues as $venue) {
            $courts = $venue->courts;
            if ($courts->isEmpty()) continue;

            foreach ($courts as $court) {
                // Create 15 bookings per court spanning over this month and last week
                for ($i = 0; $i < 15; $i++) {
                    $date = Carbon::today()->subDays(rand(-5, 20)); // Mix of past, today, and future
                    $startHour = rand(6, 21); // 6 AM to 9 PM
                    $start = str_pad($startHour, 2, '0', STR_PAD_LEFT) . ':00:00';
                    $end = str_pad($startHour + 1, 2, '0', STR_PAD_LEFT) . ':00:00';
                    
                    $status = $statuses[array_rand($statuses)];
                    
                    // If date is in the future, it can't be completed
                    if ($date->isFuture() && $status == 'completed') {
                        $status = 'confirmed';
                    }
                    
                    // Total price random between 150k and 300k
                    $price = rand(15, 30) * 10000;

                    Booking::create([
                        'court_id' => $court->id,
                        'user_id' => $customer->id,
                        'slot_date' => $date->format('Y-m-d'),
                        'start_time' => $start,
                        'end_time' => $end,
                        'total_price' => $price,
                        'status' => $status,
                        'payment_status' => ($status === 'completed' || $status === 'confirmed') ? 'paid' : 'unpaid',
                        'note' => 'Dummy booking for testing dashboard'
                    ]);
                    $count++;
                }
            }
        }

        echo "Generated {$count} dummy bookings successfully!\n";
    }
}
