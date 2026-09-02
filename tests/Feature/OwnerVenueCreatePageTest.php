<?php

namespace Tests\Feature;

use App\Models\Province;
use App\Models\Sport;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnerVenueCreatePageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_owner_can_open_create_venue_page(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->actingAs($owner);

        $response = $this->get('/owner/venues/create');

        $response->assertOk();
    }

    public function test_owner_can_submit_create_venue_form(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $sport = Sport::firstOrCreate(['slug' => 'bong-da'], [
            'name' => 'Bóng đá',
            'icon' => '⚽',
        ]);

        $province = Province::firstOrCreate(['code' => '01'], ['name' => 'Hà Nội']);
        $ward = Ward::firstOrCreate(['code' => '00001'], ['province_code' => '01', 'name' => 'Ba Đình']);

        $this->actingAs($owner);

        $response = $this->post('/owner/venues/create', [
            'sport_id' => $sport->id,
            'name' => 'Dant Sport Mỹ Đình',
            'address' => 'Mỹ Đình, Hà Nội',
            'province_code' => $province->code,
            'ward_code' => $ward->code,
            'phone' => '0912345678',
            'email' => 'venue@example.com',
            'google_maps_address' => 'Mỹ Đình, Hà Nội',
            'description' => 'Venue mới cho chủ sân',
            'lat' => '21.0302',
            'lng' => '105.7602',
            'banner' => UploadedFile::fake()->image('banner.jpg'),
            'owner_name' => 'Nguyễn Văn A',
            'citizen_id' => '001099123456',
            'business_license_number' => 'GPKD12345',
            'land_type' => 'rented',
            'bank_name' => 'MB Bank',
            'bank_account_number' => '99999999',
            'bank_account_holder' => 'NGUYEN VAN A',
            'citizen_front_image' => UploadedFile::fake()->image('cccd_front.jpg'),
            'citizen_back_image' => UploadedFile::fake()->image('cccd_back.jpg'),
            'business_license_file' => UploadedFile::fake()->create('gpkd.pdf', 100, 'application/pdf'),
            'rental_contract_file' => UploadedFile::fake()->create('hop-dong.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect('/owner/venues');
        $this->assertDatabaseHas('venues', [
            'owner_id' => $owner->id,
            'name' => 'Dant Sport Mỹ Đình',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('venue_legal_documents', [
            'venue_id' => \App\Models\Venue::where('name', 'Dant Sport Mỹ Đình')->latest('id')->value('id'),
            'land_type' => 'rented',
        ]);
    }
}
