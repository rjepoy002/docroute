<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileCompletionController extends Controller
{
    public function show(Request $request)
    {
        if ($request->user()->hasCompleteProfile()) {
            return to_route('dashboard');
        }

        return view('profile.complete', [
            'user' => $request->user(),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', Rule::exists('departments', 'id')->where('is_active', true)],
            'designation' => ['required', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:40'],
            'role' => ['prohibited'],
            'is_active' => ['prohibited'],
            'google_id' => ['prohibited'],
            'email' => ['sometimes', Rule::in([$request->user()->email])],
        ]);
        unset($data['email']);
        DB::transaction(function () use ($request, $data) {
            $department = Department::whereKey($data['department_id'])->lockForUpdate()->first();
            abort_unless($department?->is_active, 422, 'Select an active department.');
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            abort_unless($user->is_active, 403);
            $action = $user->hasCompleteProfile() ? 'profile.updated' : 'profile.completed';
            $before = $user->only(array_keys($data));
            $user->forceFill([...$data, 'profile_completed_at' => $user->profile_completed_at ?? now()])->save();
            DB::table('profile_audits')->insert([
                'user_id' => $user->id, 'action' => $action,
                'changes' => json_encode(['before' => $before, 'after' => $user->only(array_keys($data))]),
                'created_at' => now(),
            ]);
        });
        $request->user()->refresh();
        $request->session()->forget('profile_suggested_name');

        return to_route('dashboard');
    }
}
