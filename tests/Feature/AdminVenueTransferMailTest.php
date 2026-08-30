<?php

namespace Tests\Feature;

use App\Mail\AdminVenueTransferMail;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueTransferRequest;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminVenueTransferMailTest extends TestCase
{
    use DatabaseTransactions;

    public function test_notify_admin_venue_transfer_sends_email_to_admins(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin_test_transfer@example.com',
            'status' => 'active',
        ]);

        $oldOwner = User::factory()->create(['role' => 'owner', 'status' => 'active']);
        $newOwner = User::factory()->create(['role' => 'owner', 'status' => 'active']);

        $sport = Sport::create(['name' => 'Bóng đá', 'slug' => 'bong-da-mail-test']);
        $venue = Venue::create([
            'owner_id' => $oldOwner->id,
            'sport_id' => $sport->id,
            'name' => 'Sân Bóng Test Transfer Mail',
            'address' => '123 Test Street',
            'status' => 'active',
        ]);

        $transfer = VenueTransferRequest::create([
            'venue_id' => $venue->id,
            'from_owner_id' => $oldOwner->id,
            'to_owner_id' => $newOwner->id,
            'price' => 50000000,
            'status' => 'signed',
            'sender_signed_at' => now(),
            'receiver_signed_at' => now(),
        ]);

        $notificationService = app(NotificationService::class);
        $notificationService->notifyAdminVenueTransfer($transfer);

        Mail::assertSent(AdminVenueTransferMail::class, function (AdminVenueTransferMail $mail) use ($admin, $venue, $transfer) {
            return $mail->hasTo($admin->email) &&
                   $mail->transfer->id === $transfer->id &&
                   $mail->transfer->venue->name === $venue->name;
        });
    }
}
