<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Notification;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use App\Models\Wallet;
use App\Services\DebtService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DebtServiceWarningTest extends TestCase
{
    use DatabaseTransactions;

    public function test_warns_once_resets_and_warns_again_when_debt_crosses_threshold(): void
    {
        [$owner, $wallet] = $this->createOwnerWalletAndVenue(balance: -300000);
        $service = app(DebtService::class);

        $result = $service->syncDebtWarningStatus($owner->id);
        $this->assertSame('skipped', $result['action']);
        $this->assertNull($wallet->fresh()->debt_warning_sent_at);
        $this->assertSame(0, Notification::where('user_id', $owner->id)->where('type', 'debt_warning')->count());

        $wallet->update([
            'balance' => -400000,
            'debt_warning_sent_at' => null,
            'debt_warning_level' => null,
        ]);

        $result = $service->syncDebtWarningStatus($owner->id);
        $this->assertSame('warned', $result['action']);
        $this->assertNotNull($wallet->fresh()->debt_warning_sent_at);
        $this->assertEquals('80.00', $wallet->fresh()->debt_warning_level);
        $this->assertSame(1, Notification::where('user_id', $owner->id)->where('type', 'debt_warning')->count());

        $result = $service->syncDebtWarningStatus($owner->id);
        $this->assertSame('skipped', $result['action']);
        $this->assertSame(1, Notification::where('user_id', $owner->id)->where('type', 'debt_warning')->count());

        $wallet->update(['balance' => -200000]);
        $result = $service->syncDebtWarningStatus($owner->id);
        $this->assertSame('reset', $result['action']);
        $this->assertNull($wallet->fresh()->debt_warning_sent_at);
        $this->assertNull($wallet->fresh()->debt_warning_level);

        $wallet->update(['balance' => -400000]);
        $result = $service->syncDebtWarningStatus($owner->id);
        $this->assertSame('warned', $result['action']);
        $this->assertSame(2, Notification::where('user_id', $owner->id)->where('type', 'debt_warning')->count());
    }

    public function test_wallet_service_suspends_warns_and_reactivates_after_credit(): void
    {
        [$owner, $wallet, $venue] = $this->createOwnerWalletAndVenue(balance: -300000);
        $walletService = app(WalletService::class);

        $walletService->processTransaction(
            wallet: $wallet,
            type: TransactionType::COMMISSION_FEE,
            amount: 100000,
            description: 'Test commission fee'
        );

        $this->assertEquals(-400000, (int) $wallet->fresh()->balance);
        $this->assertSame('approved', $venue->fresh()->status);
        $this->assertSame(1, Notification::where('user_id', $owner->id)->where('type', 'debt_warning')->count());

        $walletService->processTransaction(
            wallet: $wallet->fresh(),
            type: TransactionType::COMMISSION_FEE,
            amount: 100000,
            description: 'Test commission fee over limit'
        );

        $this->assertEquals(-500000, (int) $wallet->fresh()->balance);
        $this->assertSame('suspended', $venue->fresh()->status);
        $this->assertSame('debt_limit_exceeded', $venue->fresh()->suspended_reason);
        $this->assertSame('approved', $venue->fresh()->status_before_debt_suspension);

        $walletService->processTransaction(
            wallet: $wallet->fresh(),
            type: TransactionType::TOPUP_CREDIT,
            amount: 200000,
            description: 'Test topup credit'
        );

        $this->assertEquals(-300000, (int) $wallet->fresh()->balance);
        $this->assertSame('approved', $venue->fresh()->status);
        $this->assertNull($venue->fresh()->suspended_reason);
        $this->assertNull($venue->fresh()->debt_suspended_at);
    }

    private function createOwnerWalletAndVenue(int $balance): array
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $wallet = Wallet::create([
            'owner_id' => $owner->id,
            'balance' => $balance,
            'available_balance' => 0,
            'pending_balance' => 0,
            'credit_limit' => 500000,
            'currency' => 'VND',
            'status' => 'active',
        ]);

        $sport = Sport::create([
            'name' => 'Test sport ' . $owner->id,
            'slug' => 'test-sport-' . $owner->id,
        ]);

        $venue = Venue::create([
            'owner_id' => $owner->id,
            'sport_id' => $sport->id,
            'name' => 'Test venue ' . $owner->id,
            'address' => 'Test address',
            'status' => 'approved',
            'auto_suspend_enabled' => true,
        ]);

        return [$owner, $wallet, $venue];
    }
}
