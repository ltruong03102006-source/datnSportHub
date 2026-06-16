<?php

namespace Tests\Feature;

use Tests\TestCase;

class OwnerAuthRouteTest extends TestCase
{
    public function test_owner_register_get_returns_helpful_json_response(): void
    {
        $response = $this->getJson('/api/owner/register');

        $response->assertStatus(405)
            ->assertJson([
                'message' => 'Use POST /api/owner/register to create an owner account.',
            ]);
    }
}
