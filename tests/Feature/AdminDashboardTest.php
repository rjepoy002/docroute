<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_displays_database_counts_and_empty_documents(): void
    {
        $department = Department::create(['name' => 'Operations', 'code' => 'OPS']);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'department_id' => $department->id]);
        User::factory()->create(['is_active' => false]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('totalUsers', 2)
            ->assertViewHas('activeUsers', 1)
            ->assertViewHas('totalDepartments', 1)
            ->assertViewHas('documentCounts', ['incoming' => 0, 'outgoing' => 0, 'pending' => 0, 'unseen' => 0, 'completed' => 0])
            ->assertSee('No documents yet')
            ->assertSee(route('logout'))
            ->assertSee(route('profile.edit'));
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_employees_cannot_access_admin_dashboard(): void
    {
        $employee = User::factory()->create(['role' => 'employee', 'is_active' => true]);
        $this->actingAs($employee)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_inactive_admin_is_logged_out(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => false]);
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_route_keeps_required_middleware(): void
    {
        $this->assertSame(['web', 'auth', 'verified', 'active', 'admin'], app('router')->getRoutes()->getByName('admin.dashboard')->gatherMiddleware());
    }
}
