<?php

namespace Tests\Feature;

use App\Mail\AdminNewVenueMail;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminNewVenueMailTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mail_is_sent_to_admins_when_owner_creates_venue(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'email' => 'admin_venue_test@example.com',
        ]);

        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
            'email' => 'owner_venue_creator@example.com',
        ]);

        $venue = Venue::create([
            'owner_id' => $owner->id,
            'name' => 'Sân Thể Thao Test',
            'slug' => 'san-the-thao-test-' . uniqid(),
            'address' => '123 Đường Test, Hà Nội',
            'status' => 'pending',
        ]);

        app(\App\Services\NotificationService::class)->notifyAdminNewVenue($venue);

        Mail::assertSent(AdminNewVenueMail::class, function ($mail) use ($admin, $venue) {
            return $mail->hasTo($admin->email) && $mail->venue->id === $venue->id;
        });
    }
}
