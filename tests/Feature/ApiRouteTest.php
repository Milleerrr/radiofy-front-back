<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ApiRouteTest extends TestCase
{
    public function testRouteRequiresAuthentication()
    {
        // Act: Attempt to access a route that requires authentication
        $response = $this->get('/search');

        // Assert: The user is not logged in and should be redirected to the login page
        $response->assertRedirect('/login');
    }

    public function testAuthenticatedExistingUserCanAccessRoute()
    {
        // Arrange: Retrieve an existing user by their username or another unique identifier
        $user = User::where('name', 'windymiller3-gb')->firstOrFail();

        // Act: Simulate the existing user making a request to a protected route
        $response = $this->actingAs($user)->get('/search');

        // Assert: The existing user is logged in and can access the route
        $response->assertStatus(200);
    }
}

