<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ContractWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_render_template_includes_dynamic_contract_details(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'name' => 'Nguyễn Văn A',
            'email' => 'owner@example.com',
            'phone' => '0900000000',
            'bank_name' => 'Vietcombank',
            'bank_account_no' => '1234567890',
            'bank_account_name' => 'NGUYEN VAN A',
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $sport = Sport::create(['name' => 'Bóng đá', 'slug' => 'bong-da']);

        $venue = Venue::create([
            'owner_id' => $owner->id,
            'sport_id' => $sport->id,
            'name' => 'Sân thử nghiệm',
            'address' => '123 Test Street',
            'status' => 'approved',
        ]);

        $contract = Contract::make([
            'owner_id' => $owner->id,
            'created_by' => $admin->id,
            'contract_code' => 'HD000001',
            'title' => 'Hợp đồng thử nghiệm',
            'commission_rate' => 10.00,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'status' => 'draft',
        ]);

        $rendered = Contract::renderTemplate($contract, $owner, $venue);

        $this->assertStringContainsString('CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM', $rendered);
        $this->assertStringContainsString('Nguyễn Văn A', $rendered);
        $this->assertStringContainsString('10.00%', $rendered);
        $this->assertStringContainsString('Sân thử nghiệm', $rendered);
    }

    public function test_admin_cannot_create_contract_for_venue_owned_by_another_owner(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $otherOwner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $sport = Sport::create(['name' => 'Bóng đá', 'slug' => 'bong-da']);

        $venue = Venue::create([
            'owner_id' => $otherOwner->id,
            'sport_id' => $sport->id,
            'name' => 'Sân thử nghiệm',
            'address' => '123 Test Street',
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.contracts.store'), [
                'owner_id' => $owner->id,
                'venue_id' => $venue->id,
                'title' => 'Hợp đồng sai chủ sân',
                'commission_rate' => 10,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
            ])
            ->assertSessionHasErrors('venue_id');
    }

    public function test_owner_cannot_view_draft_contract(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $draft = Contract::create([
            'owner_id' => $owner->id,
            'created_by' => $admin->id,
            'contract_code' => 'HD000001',
            'title' => 'Hợp đồng nháp',
            'content' => 'Nội dung thử nghiệm',
            'commission_rate' => 10.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'status' => 'draft',
        ]);

        $sent = Contract::create([
            'owner_id' => $owner->id,
            'created_by' => $admin->id,
            'contract_code' => 'HD000002',
            'title' => 'Hợp đồng đã gửi',
            'content' => 'Nội dung thử nghiệm',
            'commission_rate' => 10.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'status' => 'sent',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.contracts.index'))
            ->assertOk()
            ->assertDontSee($draft->contract_code)
            ->assertSee($sent->contract_code);

        $this->actingAs($owner)
            ->get(route('owner.contracts.show', $draft))
            ->assertForbidden();
    }

    public function test_accepting_contract_activates_owner_venue_and_applies_commission(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $sport = Sport::create(['name' => 'Bóng đá', 'slug' => 'bong-da']);

        $venue = Venue::create([
            'owner_id' => $owner->id,
            'sport_id' => $sport->id,
            'name' => 'Sân thử nghiệm',
            'address' => '123 Test Street',
            'status' => 'approved',
            'commission_rate' => null,
        ]);

        $contract = Contract::create([
            'owner_id' => $owner->id,
            'venue_id' => $venue->id,
            'created_by' => $admin->id,
            'contract_code' => 'HD000003',
            'title' => 'Hợp đồng thử nghiệm',
            'content' => 'Nội dung thử nghiệm',
            'commission_rate' => 15.00,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'status' => 'sent',
        ]);

        $this->actingAs($owner)
            ->post(route('owner.contracts.accept', $contract));

        $contract->refresh();
        $venue->refresh();

        $this->assertSame('accepted', $contract->status);
        $this->assertSame('active', $venue->status);
        $this->assertEquals(15.00, (float) $venue->commission_rate);
        $this->assertNotNull($contract->signed_at);
    }

    public function test_expired_contract_cannot_be_accepted(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $sport = Sport::create(['name' => 'Bóng đá', 'slug' => 'bong-da']);

        $venue = Venue::create([
            'owner_id' => $owner->id,
            'sport_id' => $sport->id,
            'name' => 'Sân hết hạn',
            'address' => '123 Test Street',
            'status' => 'approved',
        ]);

        $contract = Contract::create([
            'owner_id' => $owner->id,
            'venue_id' => $venue->id,
            'created_by' => $admin->id,
            'contract_code' => 'HD000004',
            'title' => 'Hợp đồng hết hạn',
            'content' => 'Nội dung thử nghiệm',
            'commission_rate' => 12.00,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
            'status' => 'sent',
        ]);

        $this->actingAs($owner)
            ->post(route('owner.contracts.accept', $contract));

        $contract->refresh();
        $venue->refresh();

        $this->assertSame('expired', $contract->status);
        $this->assertNotNull($contract->expired_at);
        $this->assertSame('approved', $venue->status);
    }

    public function test_future_contract_is_activated_by_sync_after_start_date(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $sport = Sport::create(['name' => 'Bóng đá', 'slug' => 'bong-da']);

        $venue = Venue::create([
            'owner_id' => $owner->id,
            'sport_id' => $sport->id,
            'name' => 'Sân tương lai',
            'address' => '123 Test Street',
            'status' => 'approved',
            'commission_rate' => null,
        ]);

        $startDate = Carbon::today()->addDay();

        $contract = Contract::create([
            'owner_id' => $owner->id,
            'venue_id' => $venue->id,
            'created_by' => $admin->id,
            'contract_code' => 'HD000005',
            'title' => 'Hợp đồng tương lai',
            'content' => 'Nội dung thử nghiệm',
            'commission_rate' => 18.00,
            'start_date' => $startDate->toDateString(),
            'end_date' => $startDate->copy()->addMonths(6)->toDateString(),
            'status' => 'sent',
        ]);

        $this->actingAs($owner)
            ->post(route('owner.contracts.accept', $contract));

        $contract->refresh();
        $venue->refresh();

        $this->assertSame('accepted', $contract->status);
        $this->assertSame('approved', $venue->status);

        Carbon::setTestNow($startDate);

        try {
            $this->artisan('contracts:sync-statuses')
                ->assertExitCode(0);
        } finally {
            Carbon::setTestNow();
        }

        $venue->refresh();

        $this->assertSame('active', $venue->status);
        $this->assertEquals(18.00, (float) $venue->commission_rate);
    }

    public function test_resending_rejected_contract_clears_rejection_reason(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $contract = Contract::create([
            'owner_id' => $owner->id,
            'created_by' => $admin->id,
            'contract_code' => 'HD000006',
            'title' => 'Hợp đồng gửi lại',
            'content' => 'Nội dung thử nghiệm',
            'commission_rate' => 10.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'status' => 'rejected',
            'rejection_reason' => 'Cần chỉnh lại mức hoa hồng.',
            'rejected_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.contracts.send', $contract));

        $contract->refresh();

        $this->assertSame('sent', $contract->status);
        $this->assertNull($contract->rejection_reason);
        $this->assertNull($contract->rejected_at);
        $this->assertNotNull($contract->sent_at);
    }
}
