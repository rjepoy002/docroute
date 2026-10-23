<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use App\Services\ManageAccounts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function department(bool $active = true): Department
    {
        return Department::create(['name' => 'Operations', 'code' => 'OPS', 'is_active' => $active]);
    }

    private function accountData(Department $department, array $overrides = []): array
    {
        return array_replace([
            'name' => 'Employee Example', 'email' => 'employee@example.com',
            'role' => 'employee', 'department_id' => $department->id, 'is_active' => 1,
        ], $overrides);
    }

    public function test_admin_can_create_update_deactivate_and_search_departments(): void
    {
        $this->actingAs($this->admin())->post(route('admin.departments.store'), ['name' => 'Operations', 'code' => 'ops', 'is_active' => 1])->assertRedirect(route('admin.departments.index'));
        $department = Department::where('code', 'OPS')->firstOrFail();
        $this->assertSame('OPS', $department->code);
        $this->put(route('admin.departments.update', $department), ['name' => 'Operations Office', 'code' => 'OPS', 'is_active' => 0])->assertSessionHasNoErrors();
        $this->assertFalse($department->fresh()->is_active);
        $this->get(route('admin.departments.index', ['search' => 'Office']))->assertOk()->assertSee('Operations Office');
        $this->get(route('admin.departments.create'))->assertOk();
        $this->get(route('admin.departments.edit', $department))->assertOk();
    }

    public function test_duplicate_department_names_and_codes_are_rejected(): void
    {
        $department = $this->department();
        $this->actingAs($this->admin())->post(route('admin.departments.store'), ['name' => $department->name, 'code' => 'ops', 'is_active' => 1])->assertSessionHasErrors(['name', 'code']);
        $this->assertDatabaseCount('departments', 1);
    }

    public function test_admin_can_create_edit_and_activate_accounts_with_hashed_passwords(): void
    {
        $department = $this->department();
        $data = $this->accountData($department);
        $this->actingAs($this->admin())->post(route('admin.users.store'), $data + ['password' => 'SecurePassword!123', 'password_confirmation' => 'SecurePassword!123'])->assertSessionHasNoErrors();
        $user = User::where('email', $data['email'])->firstOrFail();
        $this->assertTrue(Hash::check('SecurePassword!123', $user->password));
        $this->assertSame($department->id, $user->department_id);
        $this->put(route('admin.users.update', $user), array_replace($data, ['name' => 'Updated Employee', 'is_active' => 0]))->assertSessionHasNoErrors();
        $this->assertFalse($user->fresh()->is_active);
        $this->put(route('admin.users.update', $user), $data)->assertSessionHasNoErrors();
        $this->assertTrue($user->fresh()->is_active);
        $this->get(route('admin.users.index', ['search' => $data['email']]))->assertOk()->assertSee($data['email'])->assertDontSee($user->password);
        $this->get(route('admin.users.create'))->assertOk();
        $this->get(route('admin.users.edit', $user))->assertOk();
    }

    public function test_email_role_status_and_department_are_validated(): void
    {
        $department = $this->department();
        $admin = $this->admin();
        $user = User::factory()->create();
        $this->actingAs($admin)->put(route('admin.users.update', $user), $this->accountData($department, ['email' => $admin->email, 'role' => 'superadmin', 'is_active' => 'invalid', 'department_id' => 999]))->assertSessionHasErrors(['email', 'role', 'is_active', 'department_id']);
    }

    public function test_inactive_departments_cannot_receive_new_assignments(): void
    {
        $department = $this->department(false);
        $user = User::factory()->create(['department_id' => null, 'profile_completed_at' => null]);
        $data = $this->accountData($department);
        $this->actingAs($this->admin())->put(route('admin.users.update', $user), $data)->assertSessionHasErrors('department_id');
        $this->post(route('admin.users.store'), $data + ['password' => 'SecurePassword!123', 'password_confirmation' => 'SecurePassword!123'])->assertSessionHasErrors('department_id');
        $this->assertSame($user->department_id, $user->fresh()->department_id);
    }

    public function test_existing_inactive_department_assignment_can_be_preserved(): void
    {
        $department = $this->department(false);
        $user = User::factory()->create(['department_id' => $department->id]);
        $this->actingAs($this->admin())->put(route('admin.users.update', $user), $this->accountData($department, ['is_active' => 0]))->assertSessionHasNoErrors();
        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_admin_cannot_demote_or_deactivate_self_even_with_another_admin(): void
    {
        $department = $this->department();
        $admin = $this->admin();
        $this->admin();
        $this->actingAs($admin)->put(route('admin.users.update', $admin), $this->accountData($department, ['role' => 'employee', 'email' => $admin->email]))->assertSessionHasErrors('role');
        $this->put(route('admin.users.update', $admin), $this->accountData($department, ['role' => 'admin', 'email' => $admin->email, 'is_active' => 0]))->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->isAdmin());
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_last_admin_cannot_be_removed_through_update_or_profile_deletion(): void
    {
        $department = $this->department();
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.users.update', $admin), $this->accountData($department, ['role' => 'employee', 'email' => $admin->email]))->assertSessionHasErrors('role');
        $this->delete(route('profile.destroy'), ['password' => 'password'])->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin', 'is_active' => 1]);
    }

    public function test_admin_can_demote_another_admin_while_retaining_an_active_admin(): void
    {
        $department = $this->department();
        $actor = $this->admin();
        $target = $this->admin();
        $this->actingAs($actor)->put(route('admin.users.update', $target), $this->accountData($department))->assertSessionHasNoErrors();
        $this->assertSame('employee', $target->fresh()->role);
        $this->assertSame(1, User::where('role', 'admin')->where('is_active', true)->count());
    }

    public function test_stale_admin_cannot_remove_remaining_admin(): void
    {
        $department = $this->department();
        $staleActor = $this->admin();
        $remaining = $this->admin();
        User::whereKey($staleActor->id)->update(['role' => 'employee']);
        try {
            app(ManageAccounts::class)->save($staleActor, $this->accountData($department), $remaining);
            $this->fail('Stale administrator was authorized.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertTrue($remaining->fresh()->isAdmin());
    }

    public function test_employees_and_guests_cannot_access_any_management_endpoint(): void
    {
        $department = $this->department();
        $employee = User::factory()->create();
        $endpoints = [
            ['GET', '/admin/departments'], ['GET', '/admin/departments/create'], ['POST', '/admin/departments'],
            ['GET', '/admin/departments/'.$department->id.'/edit'], ['PUT', '/admin/departments/'.$department->id],
            ['GET', '/admin/users'], ['GET', '/admin/users/create'], ['POST', '/admin/users'],
            ['GET', '/admin/users/'.$employee->id.'/edit'], ['PUT', '/admin/users/'.$employee->id], ['PUT', '/admin/users/'.$employee->id.'/password'],
        ];
        foreach ($endpoints as [$method, $url]) {
            $this->call($method, $url)->assertRedirect(route('login'));
        }
        $this->actingAs($employee);
        foreach ($endpoints as [$method, $url]) {
            $this->call($method, $url)->assertForbidden();
        }
    }

    public function test_inactive_accounts_cannot_login_or_use_profile_and_password_endpoints(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        foreach ([['GET', '/profile'], ['PATCH', '/profile'], ['PUT', '/password'], ['GET', '/admin/users']] as [$method, $url]) {
            $this->actingAs($user)->call($method, $url)->assertRedirect(route('login'));
            $this->assertGuest();
        }
    }

    public function test_password_reset_requires_admin_confirmation_and_hashes_new_password(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $data = ['current_password' => 'incorrect', 'password' => 'NewSecurePassword!123', 'password_confirmation' => 'NewSecurePassword!123'];
        $this->actingAs($admin)->put(route('admin.users.password', $user), $data)->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->put(route('admin.users.password', $user), array_replace($data, ['current_password' => 'password', 'password_confirmation' => 'mismatch']))->assertSessionHasErrors('password');
        $oldToken = $user->getRememberToken();
        $this->put(route('admin.users.password', $user), array_replace($data, ['current_password' => 'password']))->assertRedirect(route('admin.users.edit', $user));
        $this->assertTrue(Hash::check($data['password'], $user->fresh()->password));
        $this->assertNotSame($oldToken, $user->fresh()->getRememberToken());
        $this->assertFalse(session()->hasOldInput('password'));
        $this->assertFalse(session()->hasOldInput('current_password'));
    }

    public function test_changed_password_invalidates_existing_authenticated_session(): void
    {
        $user = User::factory()->create();
        $oldHash = $user->password;
        $user->password = 'NewSecurePassword!123';
        $user->save();
        $this->actingAs($user)->withSession(['password_hash_web' => $oldHash])->get('/profile')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_password_reset_revokes_database_sessions_and_existing_reset_tokens(): void
    {
        config(['session.driver' => 'database']);
        $admin = $this->admin();
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);
        DB::table('sessions')->insert([
            'id' => 'target-session', 'user_id' => $user->id, 'payload' => base64_encode(serialize([])), 'last_activity' => time(),
        ]);
        $this->actingAs($admin)->put(route('admin.users.password', $user), [
            'current_password' => 'password', 'password' => 'NewSecurePassword!123', 'password_confirmation' => 'NewSecurePassword!123',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users.edit', $user));
        $this->assertDatabaseMissing('sessions', ['id' => 'target-session']);
        $this->assertFalse(Password::broker()->tokenExists($user, $token));
    }

    public function test_all_management_routes_require_admin_security_middleware(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            if (str_starts_with($route->getName() ?? '', 'admin.users.') || str_starts_with($route->getName() ?? '', 'admin.departments.')) {
                foreach (['auth', 'verified', 'active', 'admin'] as $middleware) {
                    $this->assertContains($middleware, $route->gatherMiddleware());
                }
            }
        }
    }

    public function test_lists_paginate_and_preserve_search(): void
    {
        User::factory()->count(16)->create(['name' => 'Matched Employee']);
        $this->actingAs($this->admin())->get(route('admin.users.index', ['search' => 'Matched']))->assertOk()->assertViewHas('users', fn ($users) => $users->total() === 16 && $users->count() === 15)->assertSee('search=Matched');
    }

    public function test_malformed_inputs_are_rejected_and_password_updates_require_reset_workflow(): void
    {
        $department = $this->department();
        $user = User::factory()->create();
        $this->actingAs($this->admin())->post(route('admin.departments.store'), ['name' => [], 'code' => [], 'is_active' => 1])->assertSessionHasErrors(['name', 'code']);
        $this->put(route('admin.users.update', $user), $this->accountData($department, ['name' => [], 'email' => [], 'password' => 'HiddenUpdate!123']))->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
