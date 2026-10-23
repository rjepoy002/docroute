<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    private function identity(string $email, string $id = 'subject-123', bool $verified = true): void
    {
        $identity = (new GoogleUser)->setRaw(['email_verified' => $verified])->map(['id' => $id, 'email' => $email, 'name' => 'Google Name']);
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($identity);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_first_login_links_without_changing_department_role_password_or_profile(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $before = $user->only(['name', 'email', 'password', 'role', 'department_id', 'is_active', 'designation', 'profile_completed_at']);
        $this->get(route('login'));
        $sessionId = session()->getId();
        $this->identity(strtoupper($user->email));
        $this->withSession(['url.intended' => route('documents.index')])->get(route('auth.google.callback', ['code' => 'valid-code']))
            ->assertRedirect(route('documents.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, session()->getId());
        $this->assertSame('subject-123', $user->fresh()->google_id);
        $this->assertEquals($before, $user->fresh()->only(array_keys($before)));
        $this->assertDatabaseHas('profile_audits', ['user_id' => $user->id, 'action' => 'google.linked']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_repeat_login_does_not_relink_or_duplicate_audits(): void
    {
        $user = User::factory()->create(['google_id' => 'subject-123']);
        $this->identity($user->email);
        $this->get(route('auth.google.callback', ['code' => 'valid-code']))->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('profile_audits', 0);
    }

    public function test_existing_account_cannot_be_rebound_to_another_google_identity(): void
    {
        $user = User::factory()->create(['google_id' => 'original-subject']);
        $this->identity($user->email);
        $this->get(route('auth.google.callback', ['code' => 'valid-code']))->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertSame('original-subject', $user->fresh()->google_id);
        $this->assertGuest();
    }

    public function test_google_identity_already_owned_by_another_account_is_rejected(): void
    {
        $owner = User::factory()->create(['google_id' => 'subject-123']);
        $target = User::factory()->create();
        $this->identity($target->email);
        $this->get(route('auth.google.callback', ['code' => 'valid-code']))->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertNull($target->fresh()->google_id);
        $this->assertSame('subject-123', $owner->fresh()->google_id);
        $this->assertGuest();
    }

    public function test_inactive_unlinked_account_cannot_be_linked(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $this->identity($user->email);
        $this->get(route('auth.google.callback', ['code' => 'valid-code']))->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertNull($user->fresh()->google_id);
        $this->assertGuest();
    }

    public function test_unverified_email_cannot_link_an_existing_account(): void
    {
        $user = User::factory()->create();
        $this->identity($user->email, verified: false);
        $this->get(route('auth.google.callback', ['code' => 'valid-code']))->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertNull($user->fresh()->google_id);
        $this->assertGuest();
    }

    public function test_denied_consent_clears_state_and_keeps_password_fallback(): void
    {
        $this->withSession(['state' => 'pending-state'])->get(route('auth.google.callback', ['error' => 'access_denied']))
            ->assertRedirect(route('login'))->assertSessionHasErrors('email')->assertSessionMissing('state');
        $this->assertGuest();
    }

    public function test_service_exception_is_not_exposed_to_user(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andThrow(new \RuntimeException('secret-token-must-not-appear'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
        $this->get(route('auth.google.callback', ['code' => 'valid-code']))->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->get(route('login'))->assertDontSee('secret-token-must-not-appear');
        $this->assertGuest();
    }

    public function test_real_socialite_state_validation_rejects_mismatch_before_network_access(): void
    {
        config(['services.google.client_id' => 'test-client', 'services.google.client_secret' => 'test-secret']);
        $this->withSession(['state' => 'original-state'])->get(route('auth.google.callback', ['code' => 'code', 'state' => 'forged-state']))
            ->assertRedirect(route('login'))->assertSessionHasErrors('email')->assertSessionMissing('state');
        $this->assertGuest();
    }

    public function test_real_socialite_consumes_valid_state_and_rejects_replay(): void
    {
        $user = User::factory()->create();
        $client = new Client(['handler' => HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'test-access', 'expires_in' => 3600])),
            new Response(200, [], json_encode(['sub' => 'subject-123', 'email' => $user->email, 'email_verified' => true, 'name' => 'Employee'])),
        ]))]);
        Socialite::shouldReceive('driver')->with('google')->andReturnUsing(function () use ($client) {
            // Each HTTP request gets a fresh provider, as under XAMPP/PHP.
            return (new GoogleProvider(request(), 'test-client', 'test-secret', 'http://localhost/docroute-v2/auth/google/callback'))
                ->setHttpClient($client);
        });
        $url = route('auth.google.callback', ['code' => 'code', 'state' => 'valid-state']);
        $this->withSession(['state' => 'valid-state'])->get($url)->assertRedirect(route('dashboard'))->assertSessionMissing('state');
        $this->assertAuthenticatedAs($user);
        $this->post(route('logout'));
        $this->get($url)->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
