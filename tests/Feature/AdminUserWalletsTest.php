<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminUserWalletsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_view_user_wallets_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $owner = User::factory()->create(['role' => 'owner', 'status' => 'active']);
        $customer = User::factory()->create(['role' => 'user', 'status' => 'active']);

        $owner->getOrCreateWallet()->update(['balance' => 500000]);
        $customer->getOrCreateWallet()->update(['balance' => 200000]);

        $this->actingAs($admin);

        $response = $this->get('/admin/user-wallets');

        $response->assertOk();
        $response->assertSee('Quản lý ví người dùng');
        $response->assertSee($owner->name);
        $response->assertSee($customer->name);
    }

    public function test_admin_can_filter_wallets_by_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $owner = User::factory()->create(['name' => 'Owner Alpha', 'role' => 'owner', 'status' => 'active']);
        $customer = User::factory()->create(['name' => 'Customer Beta', 'role' => 'user', 'status' => 'active']);

        $owner->getOrCreateWallet();
        $customer->getOrCreateWallet();

        $this->actingAs($admin);

        $ownerResponse = $this->get('/admin/user-wallets?role=owner');
        $ownerResponse->assertOk();
        $ownerResponse->assertSee('Owner Alpha');
        $ownerResponse->assertDontSee('Customer Beta');

        $customerResponse = $this->get('/admin/user-wallets?role=customer');
        $customerResponse->assertOk();
        $customerResponse->assertSee('Customer Beta');
        $customerResponse->assertDontSee('Owner Alpha');
    }

    public function test_admin_can_get_wallet_detail_json(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $user = User::factory()->create(['name' => 'User Gamma', 'role' => 'user', 'status' => 'active']);
        $wallet = $user->getOrCreateWallet();
        $wallet->update(['balance' => 350000]);

        $this->actingAs($admin);

        $response = $this->getJson("/admin/user-wallets/{$wallet->id}");

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('wallet.owner.name', 'User Gamma');
        $response->assertJsonPath('wallet.balance', 350000);
    }
}
