<?php

namespace Tests\Feature\Auth;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class ProfileCompletionTest extends TestCase
{
    use RefreshDatabase;

    private function google(?string $name = 'Google Employee', string $email = 'jane_doe-smith.test@company.test', bool $verified = true, string $id = 'google-123'): void
    {
        $identity = (new GoogleUser)->setRaw(['email_verified' => $verified, 'given_name' => trim($name ?? '') ? 'Google' : null, 'family_name' => trim($name ?? '') ? 'Employee' : null])->map([
            'id' => $id, 'name' => $name, 'email' => $email,
        ]);
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($identity);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);
        config(['onboarding.company_domains' => ['company.test']]);
    }

    public function test_google_display_name_is_used_in_editable_modal_and_email_is_readonly(): void
    {
        User::factory()->create(['email' => 'jane_doe-smith.test@company.test', 'profile_completed_at' => null]);
        $this->google();
        $this->get(route('auth.google.callback', ['code' => 'test-code']))->assertRedirect(route('profile.complete'));
        $response = $this->get(route('profile.complete'))->assertOk()->assertSee('Complete Your Profile');
        $response->assertSee('value="Google"', false)->assertSee('readonly', false);
        preg_match('/<input[^>]*name="first_name"[^>]*>/', $response->getContent(), $field);
        $this->assertNotEmpty($field);
        $this->assertStringNotContainsString('readonly', $field[0]);
        $this->assertSame('employee', User::first()->role);
    }

    public function test_missing_google_name_falls_back_to_readable_email_name(): void
    {
        User::factory()->create(['email' => 'jane_doe-smith.test@company.test', 'profile_completed_at' => null]);
        $this->google('  ');
        $this->get(route('auth.google.callback', ['code' => 'test-code']))->assertRedirect(route('profile.complete'));
        $this->get(route('profile.complete'))->assertSee('name="first_name" value=""', false);
    }

    public function test_password_signin_requires_completion_and_blocks_document_access(): void
    {
        $user = User::factory()->create(['profile_completed_at' => null]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('profile.complete'));
        $this->get('/dashboard')->assertRedirect(route('profile.complete'));
        $this->get('/documents')->assertRedirect(route('profile.complete'));
    }

    public function test_required_fields_and_active_department_are_validated(): void
    {
        $user = User::factory()->create(['profile_completed_at' => null]);
        $this->actingAs($user)->post(route('profile.complete.store'), [])->assertSessionHasErrors(['first_name', 'last_name', 'department_id', 'designation']);
        Department::whereKey($user->department_id)->update(['is_active' => false]);
        $this->post(route('profile.complete.store'), ['name' => 'Jane', 'department_id' => $user->department_id, 'designation' => 'Engineer'])->assertSessionHasErrors('department_id');
        $this->assertNull($user->fresh()->profile_completed_at);
    }

    public function test_edited_name_saves_and_dashboard_is_immediately_available_without_approval(): void
    {
        $user = User::factory()->create(['profile_completed_at' => null]);
        $this->actingAs($user)->post(route('profile.complete.store'), [
            'first_name' => 'Edited Employee', 'last_name' => 'Name', 'department_id' => $user->department_id, 'designation' => 'Engineer',
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
        $this->assertTrue($user->fresh()->hasCompleteProfile());
        $this->assertSame('Edited Employee Name', $user->fresh()->name);
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('profile.complete'))->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('profile_audits', ['user_id' => $user->id, 'action' => 'profile.completed']);
    }

    public function test_personal_gmail_and_unconfigured_domains_cannot_register(): void
    {
        $this->google(null, 'someone@gmail.com');
        config(['onboarding.company_domains' => ['gmail.com']]);
        $this->get(route('auth.google.callback', ['code' => 'test-code']))->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_company_onboarding_is_disabled_by_default(): void
    {
        $this->google();
        config(['onboarding.company_domains' => []]);
        $this->get(route('auth.google.callback', ['code' => 'test-code']))->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_verified_matching_email_links_an_existing_account(): void
    {
        $user = User::factory()->create(['email' => 'employee@company.test']);
        $this->google('Employee', $user->email);
        $this->get(route('auth.google.callback', ['code' => 'test-code']))->assertRedirect(route('dashboard'));
        $this->assertSame('google-123', $user->fresh()->google_id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_unverified_google_email_cannot_onboard(): void
    {
        $this->google('Employee', 'employee@company.test', false);
        $this->get(route('auth.google.callback', ['code' => 'test-code']))->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_profile_submission_cannot_escalate_role_or_change_email(): void
    {
        $user = User::factory()->create(['profile_completed_at' => null]);
        $data = ['name' => 'Employee', 'department_id' => $user->department_id, 'designation' => 'Engineer'];
        $this->actingAs($user)->post(route('profile.complete.store'), [...$data, 'role' => 'admin', 'is_active' => true])->assertSessionHasErrors(['role', 'is_active']);
        $this->post(route('profile.complete.store'), [...$data, 'email' => 'attacker@company.test'])->assertSessionHasErrors('email');
        $this->assertSame('employee', $user->fresh()->role);
        $this->assertNull($user->fresh()->profile_completed_at);
    }

    public function test_linked_complete_employee_skips_modal(): void
    {
        $user = User::factory()->create(['google_id' => 'google-123']);
        $this->google('Google Name', $user->email);
        $this->get(route('auth.google.callback', ['code' => 'test-code']))->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_oauth_state_fails_closed(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andThrow(new InvalidStateException);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
        $this->get(route('auth.google.callback', ['code' => 'test-code']))->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_existing_employee_can_explicitly_link_google_from_authenticated_session(): void
    {
        $user = User::factory()->create();
        $this->google('Employee', $user->email);
        $this->actingAs($user)->withSession(['google_link_user' => $user->id])
            ->get(route('auth.google.callback', ['code' => 'test-code']))->assertRedirect(route('dashboard'));
        $this->assertSame('google-123', $user->fresh()->google_id);
        $this->assertDatabaseHas('profile_audits', ['user_id' => $user->id, 'action' => 'google.linked']);
    }

    public function test_inactive_linked_employee_cannot_sign_in(): void
    {
        $user = User::factory()->create(['google_id' => 'google-123', 'is_active' => false]);
        $this->google('Employee', $user->email);
        $this->get(route('auth.google.callback', ['code' => 'test-code']))->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_profile_changes_are_audited_and_email_remains_unchanged(): void
    {
        $user = User::factory()->create();
        $email = $user->email;
        $this->actingAs($user)->post(route('profile.complete.store'), [
            'first_name' => 'New', 'last_name' => 'Name', 'department_id' => $user->department_id,
            'designation' => 'New Position', 'contact_number' => '09171234567',
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
        $this->assertSame($email, $user->fresh()->email);
        $this->assertDatabaseHas('profile_audits', ['user_id' => $user->id, 'action' => 'profile.updated']);
    }
}
