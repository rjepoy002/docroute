<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Support\DocumentOverview;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, DocumentOverview $documents): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'totalDepartments' => Department::count(),
            'documentCounts' => $documents->forUser($request->user()),
            'recentDocuments' => $documents->recentForUser($request->user()),
        ]);
    }
}
