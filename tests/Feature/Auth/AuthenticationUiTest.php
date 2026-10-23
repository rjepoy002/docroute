<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_uses_branded_layout_and_working_recovery_link(): void
    {
        $this->get(route('login'))->assertOk()
            ->assertSee('Welcome Back')->assertSee('Official PALECO Email Recommended')
            ->assertSee('Powering Better')->assertSee(route('password.request'))
            ->assertSee('data-auth-form', false)->assertSee('Show password');
    }

    public function test_google_button_uses_existing_oauth_route_when_configured(): void
    {
        config(['services.google.client_id' => 'ui-test-client', 'services.google.client_secret' => 'ui-test-secret', 'services.google.redirect' => 'http://localhost/auth/google/callback']);
        $this->get(route('login'))->assertSee('Continue with Google')->assertSee(route('google.redirect'));
        $response = $this->get(route('google.redirect'));
        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith('https://accounts.google.com/', $location);
        parse_str(parse_url($location, PHP_URL_QUERY), $parameters);
        $this->assertSame('ui-test-client', $parameters['client_id']);
        $this->assertNotEmpty($parameters['state']);
    }

    public function test_unconfigured_google_button_is_not_presented_as_an_action(): void
    {
        config(['services.google.client_id' => null]);
        $this->get(route('login'))->assertDontSee('Continue with Google');
    }
}
