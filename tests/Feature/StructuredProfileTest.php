<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DocumentWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class StructuredProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_names_are_normalized_without_changing_legacy_name_or_authorization(): void
    {
        $user = User::factory()->create(['name' => 'Preserved Original Name', 'google_id' => 'google-subject']);
        $before = $user->only(['department_id', 'role', 'email', 'google_id', 'password', 'is_active']);
        $this->actingAs($user)->patchJson(route('profile.update'), ['first_name' => ' Juan ', 'last_name' => ' Dela Cruz ', 'middle_initial' => ' d. '])
            ->assertOk()->assertJsonPath('display_name', 'Juan D. Dela Cruz');
        $user->refresh();
        $this->assertSame('D', $user->middle_initial);
        $this->assertSame('Juan D. Dela Cruz', $user->name);
        $this->assertSame('Preserved Original Name', $user->getRawOriginal('name'));
        $this->assertEquals($before, $user->only(array_keys($before)));
        $this->assertDatabaseHas('profile_audits', ['user_id' => $user->id, 'action' => 'profile.updated']);
    }

    public function test_middle_initial_may_be_blank_and_names_need_not_be_split(): void
    {
        $user = User::factory()->create();
        foreach ([null, '', 'd', 'D.'] as $initial) {
            $expected = filled($initial) ? 'Maria Luisa D. Dela Cruz' : 'Maria Luisa Dela Cruz';
            $this->actingAs($user)->patchJson(route('profile.update'), ['first_name' => 'Maria Luisa', 'last_name' => 'Dela Cruz', 'middle_initial' => $initial])
                ->assertOk()->assertJsonPath('display_name', $expected);
        }
    }

    public function test_invalid_names_reject_whitespace_long_names_and_middle_names(): void
    {
        $user = User::factory()->create();
        $valid = ['first_name' => 'Juan', 'last_name' => 'Cruz'];
        foreach ([['first_name' => '   '], ['last_name' => '  '], ['first_name' => str_repeat('a', 101)], ['last_name' => str_repeat('b', 101)], ['middle_initial' => 'David'], ['middle_initial' => 'D..'], ['middle_initial' => '9']] as $invalid) {
            $this->actingAs($user)->patchJson(route('profile.update'), array_replace($valid, $invalid))->assertUnprocessable()->assertJsonValidationErrors(array_keys($invalid));
        }
        $this->assertNull($user->fresh()->first_name);
    }

    public function test_self_service_rejects_privileged_fields_and_user_identifiers(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $valid = ['first_name' => 'Juan', 'last_name' => 'Cruz'];
        foreach (['role' => 'admin', 'department_id' => $other->department_id, 'user_id' => $other->id, 'id' => $other->id, 'email' => 'changed@example.test', 'password' => 'new-password', 'google_id' => 'replacement', 'is_admin' => true, 'approval_status' => 'approved', 'is_active' => true] as $field => $value) {
            $this->actingAs($user)->patchJson(route('profile.update'), [...$valid, $field => $value])->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->assertNull($other->fresh()->first_name);
        $this->assertNull($user->fresh()->first_name);
        $this->patchJson('/profile/'.$other->id, $valid)->assertNotFound();
    }

    public function test_guests_cannot_update_profiles(): void
    {
        $this->patchJson(route('profile.update'), ['first_name' => 'Juan', 'last_name' => 'Cruz'])->assertUnauthorized();
    }

    public function test_legacy_users_can_login_and_keep_legacy_display_fallback(): void
    {
        $user = User::factory()->create(['name' => 'Maria Luisa Dela Cruz', 'first_name' => 'Maria Luisa']);
        $this->assertSame('Maria Luisa Dela Cruz', $user->display_name);
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_profile_settings_are_modal_and_legacy_route_redirects(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('profile.edit'))->assertRedirect(route('dashboard', ['profile' => 'settings']));
        $this->get(route('dashboard'))->assertOk()->assertSee('data-open-profile', false)->assertSee('id="profile-settings"', false)->assertSee('data-profile-settings-form', false);
    }

    public function test_name_changes_preserve_document_relationships_and_audit_snapshots(): void
    {
        $user = User::factory()->create();
        $recipient = User::factory()->create(['department_id' => $user->department_id]);
        $document = app(DocumentWorkflow::class)->create($user, ['title' => 'Unchanged history', 'department_id' => $recipient->department_id, 'recipient_id' => $recipient->id]);
        $history = $document->events()->get()->toArray();
        $route = $document->currentRoute->toArray();
        $this->actingAs($user)->patchJson(route('profile.update'), ['first_name' => 'Updated', 'last_name' => 'Employee'])->assertOk();
        $this->assertSame($user->id, $document->fresh()->created_by);
        $this->assertEquals($route, $document->fresh()->currentRoute->toArray());
        $this->assertEquals($history, $document->events()->get()->toArray());
    }

    public function test_google_structured_names_are_suggestions_and_do_not_overwrite_manual_names(): void
    {
        $user = User::factory()->create(['first_name' => 'Manual Given', 'last_name' => 'Dela Cruz', 'middle_initial' => 'M', 'google_id' => 'subject']);
        $identity = (new GoogleUser)->setRaw(['email_verified' => true, 'given_name' => 'Google Given', 'family_name' => 'Google Family'])->map(['id' => 'subject', 'email' => $user->email, 'name' => 'Unsplit Google Name']);
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($identity);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
        $this->get(route('auth.google.callback', ['code' => 'valid-code']))->assertRedirect(route('dashboard'))->assertSessionHas('google_name_suggestions.first_name', 'Google Given');
        $this->assertSame('Manual Given M. Dela Cruz', $user->fresh()->name);
        $this->get(route('dashboard'))->assertSee('Manual Given')->assertSee('Dela Cruz');
    }
}
