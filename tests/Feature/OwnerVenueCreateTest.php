<?php

namespace Tests\Feature;

use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
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
