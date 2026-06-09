<?php

namespace Tests\Feature;

use Tests\TestCase;

class OwnerRegisterRouteTest extends TestCase
{
    public function test_owner_registration_web_route_is_unique_and_points_to_web_page(): void
    {
        $this->assertStringEndsWith('/owner/register', route('owner.register.page'));
    }
}
