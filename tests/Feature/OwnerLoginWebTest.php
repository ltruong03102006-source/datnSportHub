<?php

namespace Tests\Feature;

use App\Models\OwnerRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerLoginWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_login_page_is_available(): void
    {
        $response = $this->get('/owner/login');

        $response->assertOk();
        $response->assertSee('Đăng nhập chủ sân');
    }

    public function test_owner_can_login_from_web_page(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $response = $this->post('/owner/login', [
            'email' => $owner->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/owner/register');
        $this->assertAuthenticatedAs($owner);
    }

    public function test_user_with_owner_registration_can_login_as_owner(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        OwnerRegistration::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '0900000000',
            'status' => 'pending',
        ]);

        $response = $this->post('/owner/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/owner/register');
        $this->assertAuthenticatedAs($user);
        $this->assertSame('owner', $user->fresh()->role);
    }
}
