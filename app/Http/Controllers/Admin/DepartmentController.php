<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? '';
        $departments = Department::withCount('users')->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%')))->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.departments.index', compact('departments', 'search'));
    }

    public function create()
    {
        return view('admin.departments.form', ['department' => new Department(['is_active' => true])]);
    }

    public function store(DepartmentRequest $request)
    {
        Department::create($request->validated());

        return to_route('admin.departments.index')->with('success', 'Department created.');
    }

    public function edit(Department $department)
    {
        return view('admin.departments.form', compact('department'));
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        DB::transaction(function () use ($department, $request) {
            Department::whereKey($department->id)->lockForUpdate()->firstOrFail()->update($request->validated());
        }, 3);

        return to_route('admin.departments.index')->with('success', 'Department updated.');
    }
}
