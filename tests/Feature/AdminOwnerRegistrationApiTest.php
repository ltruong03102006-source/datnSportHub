<?php

namespace Tests\Feature;

use App\Models\OwnerRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOwnerRegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_owner_registrations_with_status_filter(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        OwnerRegistration::create([
            'user_id' => User::factory()->create(['role' => 'user', 'status' => 'active'])->id,
            'name' => 'Nguyen Van A',
            'email' => 'owner@example.com',
            'phone' => '0123456789',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/owner-registrations?status=pending');

        $response->assertOk();
        $response->assertJsonPath('data.0.status', 'pending');
        $response->assertJsonPath('data.0.name', 'Nguyen Van A');
    }

    public function test_admin_can_view_owner_registration_detail(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $registration = OwnerRegistration::create([
            'user_id' => $user->id,
            'name' => 'Nguyen Van B',
            'email' => 'owner2@example.com',
            'phone' => '0987654321',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/admin/owner-registrations/{$registration->id}");

        $response->assertOk();
        $response->assertJsonPath('data.id', $registration->id);
        $response->assertJsonPath('data.user.email', $user->email);
    }

    public function test_admin_can_approve_owner_registration(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $registration = OwnerRegistration::create([
            'user_id' => $user->id,
            'name' => 'Nguyen Van C',
            'email' => 'owner3@example.com',
            'phone' => '0123456789',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/owner-registrations/{$registration->id}/approve");

        $response->assertOk();
        $response->assertJson(['message' => 'Owner account approved successfully']);

        $registration->refresh();
        $user->refresh();

        $this->assertSame('active', $registration->status);
        $this->assertSame('owner', $user->role);
        $this->assertSame('active', $user->status);
    }

    public function test_admin_can_approve_owner_registration_when_user_id_is_missing_but_email_matches(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
            'email' => 'owner-missing-user@example.com',
        ]);

        $registration = OwnerRegistration::create([
            'user_id' => null,
            'name' => 'Nguyen Van E',
            'email' => $user->email,
            'phone' => '0123456789',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/owner-registrations/{$registration->id}/approve");

        $response->assertOk();
        $response->assertJson(['message' => 'Owner account approved successfully']);

        $registration->refresh();
        $user->refresh();

        $this->assertSame('active', $registration->status);
        $this->assertSame('owner', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertSame($user->id, $registration->user_id);
    }

    public function test_admin_can_create_user_when_approving_registration_without_existing_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $registration = OwnerRegistration::create([
            'user_id' => null,
            'name' => 'Nguyen Van F',
            'email' => 'owner-missing-account@example.com',
            'phone' => '0123456789',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/owner-registrations/{$registration->id}/approve");

        $response->assertOk();
        $response->assertJson(['message' => 'Owner account approved successfully']);

        $registration->refresh();

        $this->assertSame('active', $registration->status);
        $this->assertNotNull($registration->user_id);

        $user = User::where('email', $registration->email)->first();
        $this->assertNotNull($user);
        $this->assertSame('owner', $user->role);
        $this->assertSame('active', $user->status);
    }

    public function test_admin_can_reject_owner_registration(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $registration = OwnerRegistration::create([
            'user_id' => $user->id,
            'name' => 'Nguyen Van D',
            'email' => 'owner4@example.com',
            'phone' => '0123456789',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/owner-registrations/{$registration->id}/reject", [
                'reason' => 'Thông tin không hợp lệ',
            ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Owner account rejected successfully']);

        $registration->refresh();
        $this->assertSame('rejected', $registration->status);
        $this->assertSame('Thông tin không hợp lệ', $registration->rejection_reason);
        $this->assertSame('user', $user->role);
    }

    public function test_non_admin_cannot_access_admin_owner_registration_routes(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/owner-registrations');

        $response->assertForbidden();
    }
}
