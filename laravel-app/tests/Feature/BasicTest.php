<?php

namespace Tests\Feature;

use Tests\TestCase;

class BasicTest extends TestCase
{
    /**
     * Test that the application returns a successful response.
     */
    public function test_application_returns_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302); // Redirects to dashboard
    }

    /**
     * Test that the dashboard loads successfully.
     */
    public function test_dashboard_loads(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test that the bills index page loads.
     */
    public function test_bills_index_loads(): void
    {
        $response = $this->get('/bills');

        $response->assertStatus(200);
    }
}
