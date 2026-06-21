<?php

namespace Tests\Feature;

use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueLegalDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnerVenueCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_venue_with_banner_and_pending_status(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $sport = Sport::create([
            'name' => 'Badminton',
            'slug' => 'badminton',
        ]);

        $response = $this->actingAs($owner, 'sanctum')->postJson('/api/owner/venues', [
            'sport_id' => $sport->id,
            'name' => 'Dant Sport Mỹ Đình',
            'address' => '123 Nguyễn Văn Huyên',
            'description' => 'Venue for badminton',
            'banner' => UploadedFile::fake()->image('banner.png', 600, 400)->size(120),
            'lat' => 21.0285,
            'lng' => 105.8542,
            'phone' => '0123456789',
            'email' => 'venue@example.com',
            'open_hours' => '06:00',
            'close_hours' => '22:00',
            'google_maps_address' => '123 Nguyễn Văn Huyên, Hà Nội',
            'owner_name' => 'Nguyễn Văn A',
            'citizen_id' => '001234567890',
            'business_license_number' => 'BL-001',
            'bank_name' => 'Techcombank',
            'bank_account_number' => '123456789',
            'bank_account_holder' => 'Nguyễn Văn A',
            'citizen_front_image' => UploadedFile::fake()->image('front.png', 600, 400)->size(120),
            'citizen_back_image' => UploadedFile::fake()->image('back.png', 600, 400)->size(120),
            'business_license_file' => UploadedFile::fake()->image('license.png', 600, 400)->size(120),
            'rental_contract_file' => UploadedFile::fake()->image('contract.png', 600, 400)->size(120),
            'land_certificate_file' => UploadedFile::fake()->image('land.png', 600, 400)->size(120),
        ]);

        $response->assertCreated();
        $response->assertJsonPath('message', 'Venue created successfully');
        $response->assertJsonPath('data.name', 'Dant Sport Mỹ Đình');

        $venue = Venue::latest('id')->first();
        $this->assertNotNull($venue);
        $this->assertEquals($owner->id, $venue->owner_id);
        $this->assertEquals('pending', $venue->status);
        $this->assertNotNull($venue->banner);
        $this->assertTrue(Storage::disk('public')->exists($venue->banner));

        $legalDocument = VenueLegalDocument::where('venue_id', $venue->id)->first();
        $this->assertNotNull($legalDocument);
        $this->assertEquals('pending', $legalDocument->status);
    }

    public function test_owner_cannot_create_court_for_unapproved_venue_via_api(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $sport = Sport::create([
            'name' => 'Badminton',
            'slug' => 'badminton',
        ]);

        $venue = Venue::create([
            'owner_id' => $owner->id,
            'sport_id' => $sport->id,
            'name' => 'Venue chưa duyệt',
            'address' => '123 Test Street',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($owner, 'sanctum')
            ->postJson('/api/owner/venues/' . $venue->id . '/courts', [
                'name' => 'Sân 1',
                'type' => 'indoor',
                'description' => 'Test court',
            ]);

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Bạn phải được Admin duyệt cơ sở trước khi tạo sân.');
    }

    public function test_invalid_sport_id_returns_validation_error(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $response = $this->actingAs($owner, 'sanctum')->postJson('/api/owner/venues', [
            'sport_id' => 999999,
            'name' => 'Venue',
            'address' => 'Address',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['sport_id']);
    }

    public function test_non_owner_user_receives_forbidden_response(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/owner/venues', [
            'sport_id' => 1,
            'name' => 'Venue',
            'address' => 'Address',
        ]);

        $response->assertForbidden();
    }
}
