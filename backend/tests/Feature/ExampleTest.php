<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The root route redirects guests to the dashboard login page.
     */
    public function test_root_redirects_guests_to_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302)->assertRedirect(route('login', absolute: false));
    }
}
