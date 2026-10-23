<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use App\Services\DocumentWorkflow;
use Database\Seeders\LegacyDocRouteDepartmentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentMasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_departments_are_correct_active_and_idempotent(): void
    {
        $expected = [
            'ACOD' => 'Area Central Operations Department',
            'ANOD' => 'Area North Operations Department',
            'ASOD' => 'Area South Operations Department',
            'CPD' => 'Corporate Planning Department',
            'FSD' => 'Finance Services Department',
            'ISD' => 'Institutional Services Department',
            'IAD' => 'Internal Audit Department',
            'OGM' => 'Office of the General Manager',
            'TSD' => 'Technical Services Department',
        ];
        $existing = Department::create(['code' => 'FSD', 'name' => 'Old Finance', 'is_active' => false]);
        $this->seed(LegacyDocRouteDepartmentsSeeder::class);
        $ids = Department::orderBy('code')->pluck('id', 'code')->all();
        $this->seed(LegacyDocRouteDepartmentsSeeder::class);
        $this->assertSame($ids, Department::orderBy('code')->pluck('id', 'code')->all());
        $this->assertDatabaseCount('departments', 9);
        $this->assertSame('FSD', $existing->fresh()->code);
        foreach ($expected as $code => $name) {
            $this->assertDatabaseHas('departments', compact('code', 'name') + ['is_active' => true]);
        }
    }

    public function test_ict_cleanup_preserves_user_and_document_relationships(): void
    {
        $ict = Department::create(['code' => 'ICT', 'name' => 'Information and Communications Technology', 'is_active' => true]);
        $user = User::factory()->create(['department_id' => $ict->id]);
        $recipient = User::factory()->create(['department_id' => $ict->id]);
        $document = app(DocumentWorkflow::class)->create($user, [
            'title' => 'Preserved routing', 'department_id' => $ict->id, 'recipient_id' => $recipient->id,
        ]);
        $routeId = $document->current_route_id;
        $this->seed(LegacyDocRouteDepartmentsSeeder::class);
        $this->assertSame('CPD', $ict->fresh()->code);
        $this->assertSame($ict->id, $user->fresh()->department_id);
        $this->assertSame($ict->id, $recipient->fresh()->department_id);
        $this->assertSame($routeId, $document->fresh()->current_route_id);
        $this->assertSame($ict->id, $document->fresh()->currentRoute->to_department_id);
        $this->assertSame($ict->id, $document->fresh()->currentRoute->from_department_id);
        $this->assertDatabaseMissing('departments', ['code' => 'ICT']);
        $this->assertDatabaseCount('departments', 9);
    }

    public function test_dynamic_dropdowns_show_codes_and_full_names(): void
    {
        $this->seed(LegacyDocRouteDepartmentsSeeder::class);
        $department = Department::where('code', 'FSD')->firstOrFail();
        $user = User::factory()->create(['department_id' => $department->id, 'role' => 'admin']);
        $label = 'FSD – Finance Services Department';
        $this->assertSame($label, $department->display_name);
        foreach (['profile.edit', 'admin.users.create', 'admin.departments.index', 'documents.create', 'documents.index'] as $route) {
            $this->actingAs($user)->get(route($route))->assertOk()->assertSee($label);
        }
        $this->get(route('documents.create'))->assertSee('value="'.$department->id.'"', false);
        $incomplete = User::factory()->create(['profile_completed_at' => null, 'department_id' => $department->id]);
        $this->actingAs($incomplete)->get(route('profile.complete'))->assertOk()->assertSee($label);
        $document = app(DocumentWorkflow::class)->create($user, [
            'title' => 'Forward dropdown', 'department_id' => $department->id, 'recipient_id' => $user->id,
        ]);
        $this->actingAs($user)->put(route('documents.receive', $document), ['route_id' => $document->current_route_id])->assertRedirect();
        $this->get(route('documents.show', $document))->assertOk()->assertSee('id="forward"', false)->assertSee($label);
    }

    public function test_employees_cannot_manage_department_records(): void
    {
        $user = User::factory()->create();
        $department = $user->department;
        $this->actingAs($user)->get(route('admin.departments.create'))->assertForbidden();
        $this->post(route('admin.departments.store'), ['code' => 'BAD', 'name' => 'Unauthorized', 'is_active' => true])->assertForbidden();
        $this->put(route('admin.departments.update', $department), ['code' => 'BAD', 'name' => 'Unauthorized', 'is_active' => false])->assertForbidden();
        $this->assertTrue($department->fresh()->is_active);
    }
}
