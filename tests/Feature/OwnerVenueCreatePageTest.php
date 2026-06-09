<?php

namespace Tests\Feature;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerVenueCreatePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_create_venue_page(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->actingAs($owner);

        $response = $this->get('/owner/venues/create');

        $response->assertOk();
        $response->assertSee('Tạo venue mới');
    }

    public function test_owner_can_submit_create_venue_form(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'status' => 'active',
        ]);

        $sport = Sport::create([
            'name' => 'Bóng đá',
            'slug' => 'bong-da',
            'icon' => '⚽',
        ]);

        $this->actingAs($owner);

        $response = $this->post('/owner/venues/create', [
            'sport_id' => $sport->id,
            'name' => 'Dant Sport Mỹ Đình',
            'address' => 'Mỹ Đình, Hà Nội',
            'description' => 'Venue mới cho chủ sân',
            'lat' => '21.0302',
            'lng' => '105.7602',
        ]);

        $response->assertRedirect('/owner/venues/create');
        $this->assertDatabaseHas('venues', [
            'owner_id' => $owner->id,
            'name' => 'Dant Sport Mỹ Đình',
            'status' => 'pending',
        ]);
    }
}
