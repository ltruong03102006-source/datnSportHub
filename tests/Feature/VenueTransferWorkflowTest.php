<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueTransferRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VenueTransferWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_approving_transfer_does_not_activate_venue_until_new_owner_signs_contract(): void
    {
        $oldOwner = User::factory()->create([
            'role' => 'owner',
            'name' => 'Chủ Cũ',
            'status' => 'active',
        ]);

        $newOwner = User::factory()->create([
            'role' => 'owner',
            'name' => 'Chủ Mới',
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $sport = Sport::create(['name' => 'Bóng đá', 'slug' => 'bong-da-transfer']);

        $venue = Venue::create([
            'owner_id' => $oldOwner->id,
            'sport_id' => $sport->id,
            'name' => 'Sân Chuyển Nhượng',
            'address' => '456 Tran Hung Dao',
            'status' => 'active',
        ]);

        // Tạo hợp đồng cũ của chủ cũ
        $oldContract = Contract::create([
            'owner_id' => $oldOwner->id,
            'venue_id' => $venue->id,
            'created_by' => $admin->id,
            'contract_code' => 'HD000099',
            'title' => 'Hợp đồng cũ',
            'content' => 'Nội dung',
            'commission_rate' => 10.00,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'status' => 'accepted',
        ]);

        // Tạo yêu cầu chuyển nhượng ở trạng thái đã ký bởi 2 bên, chờ Admin duyệt
        $transfer = VenueTransferRequest::create([
            'venue_id' => $venue->id,
            'from_owner_id' => $oldOwner->id,
            'to_owner_id' => $newOwner->id,
            'status' => 'signed',
            'sender_signed_at' => now(),
            'receiver_signed_at' => now(),
            'receiver_data' => [
                'owner_name' => 'Chủ Mới',
                'phone' => '0988888888',
                'email' => 'chumoi@example.com',
                'citizen_id' => '123456789012',
                'address' => '789 Le Loi',
            ],
        ]);

        // 1. ADMIN PHÊ DUYỆT CHUYỂN NHƯỢNG
        $response = $this->actingAs($admin)
            ->from(route('admin.venue-transfers.show', $transfer->id))
            ->post(route('admin.venue-transfers.approve', $transfer));

        $response->assertSessionMissing('error');
        $response->assertRedirect(route('admin.venue-transfers.show', $transfer->id));

        $venue->refresh();
        $oldContract->refresh();

        // Kiểm tra cơ sở đã đổi chủ, hợp đồng cũ bị chấm dứt, và status là 'approved' (CHƯA PHẢI 'active')
        $this->assertEquals($newOwner->id, $venue->owner_id);
        $this->assertSame('approved', $venue->status);
        $this->assertSame('terminated', $oldContract->status);

        // 2. ADMIN TẠO VÀ GỬI HỢP ĐỒNG MỚI CHO CHỦ MỚI
        $newContract = Contract::create([
            'owner_id' => $newOwner->id,
            'venue_id' => $venue->id,
            'created_by' => $admin->id,
            'contract_code' => 'HD000100',
            'title' => 'Hợp đồng hợp tác mới',
            'content' => 'Nội dung',
            'commission_rate' => 12.00,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addMonths(12)->toDateString(),
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.contracts.send', $newContract));

        $newContract->refresh();
        $this->assertSame('sent', $newContract->status);

        // Trước khi ký HĐ mới, venue vẫn chưa active
        $venue->refresh();
        $this->assertSame('approved', $venue->status);

        // 3. CHỦ MỚI KÝ HỢP ĐỒNG MỚI
        $this->actingAs($newOwner)
            ->post(route('owner.contracts.accept', $newContract));

        $newContract->refresh();
        $venue->refresh();

        // Sau khi ký HĐ mới, cơ sở MỚI CHÍNH THỨC SANG 'active'
        $this->assertSame('accepted', $newContract->status);
        $this->assertSame('active', $venue->status);
    }

    public function test_unapproved_or_uncontracted_venue_cannot_be_viewed_or_booked_by_customers(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $customer = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $sport = Sport::create(['name' => 'Cầu lông', 'slug' => 'cau-long-test']);

        // Cơ sở ở trạng thái 'approved' (chưa ký HĐ mới với Admin)
        $venue = Venue::create([
            'owner_id' => $owner->id,
            'sport_id' => $sport->id,
            'name' => 'Sân Cầu Lông Chờ Ký HĐ',
            'address' => '789 Nam Kỳ Khởi Nghĩa',
            'status' => 'approved',
        ]);

        $court = $venue->courts()->create([
            'name' => 'Sân số 1',
            'status' => 'active',
            'is_bookable_online' => true,
        ]);

        // 1. Khách hàng không thể xem chi tiết cơ sở khi chưa active
        $this->actingAs($customer)
            ->get(route('venues.show', $venue->id))
            ->assertNotFound();

        // 2. Khách hàng không thể xem trang đặt sân con khi venue chưa active
        $this->actingAs($customer)
            ->get(route('web.courts.booking', $court->id))
            ->assertNotFound();

        // 3. API đặt sân thất bại nếu venue chưa active
        $this->actingAs($customer)
            ->postJson(route('web.courts.booking.store'), [
                'court_id' => $court->id,
                'slot_date' => now()->addDay()->toDateString(),
                'start_time' => '08:00',
                'end_time' => '09:00',
            ])
            ->assertStatus(403);
    }
}
