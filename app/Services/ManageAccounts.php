<?php

namespace App\Services;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManageAccounts
{
    public function save(User $actor, array $data, ?User $target = null): User
    {
        return DB::transaction(function () use ($actor, $data, $target) {
            // Lock department before users consistently to serialize assignment and deactivation.
            $department = Department::whereKey($data['department_id'])->lockForUpdate()->first();
            if (! $department) {
                throw ValidationException::withMessages(['department_id' => 'Select an active department.']);
            }

            // Lock in primary-key order: concurrent demotions cannot both remove the last admin.
            $users = User::orderBy('id')->lockForUpdate()->get();
            $currentActor = $users->firstWhere('id', $actor->id);
            abort_unless($currentActor?->isAdmin() && $currentActor->is_active, 403);
            $user = $target ? $users->firstWhere('id', $target->id) : new User;
            abort_unless($user, 404);
            if (! $department->is_active && (! $user->exists || $user->department_id !== $department->id)) {
                throw ValidationException::withMessages(['department_id' => 'Select an active department.']);
            }
            $removingAdmin = $user->exists && $user->isAdmin() && $user->is_active
                && ($data['role'] !== 'admin' || ! $data['is_active']);
            if ($user->id === $actor->id && ($data['role'] !== 'admin' || ! $data['is_active'])) {
                throw ValidationException::withMessages(['role' => 'You cannot deactivate or demote your own account.']);
            }
            if ($removingAdmin && $users->filter(fn (User $item) => $item->isAdmin() && $item->is_active)->count() <= 1) {
                throw ValidationException::withMessages(['role' => 'At least one active administrator must remain.']);
            }
            $before = $user->only(['name', 'email', 'department_id', 'role', 'is_active']);
            $created = ! $user->exists;
            $user->fill($data);
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
            $user->save();
            DB::table('profile_audits')->insert([
                'user_id' => $user->id,
                'action' => $created ? 'profile.created' : 'profile.updated',
                'changes' => json_encode(['actor_id' => $actor->id, 'before' => $before, 'after' => $user->only(['name', 'email', 'department_id', 'role', 'is_active'])]),
                'created_at' => now(),
            ]);

            return $user;
        }, 3);
    }
}
