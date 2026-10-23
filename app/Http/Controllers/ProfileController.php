<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Document;
use App\Models\DocumentEvent;
use App\Models\DocumentRoute;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): RedirectResponse
    {
        return to_route('dashboard', ['profile' => 'settings']);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request)
    {
        $data = ProfileUpdateRequest::normalizeNames($request->validated());
        $user = DB::transaction(function () use ($request, $data) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            abort_unless($user->is_active, 403);
            $before = $user->only(['first_name', 'last_name', 'middle_initial']);
            $user->forceFill($data)->save();
            DB::table('profile_audits')->insert([
                'user_id' => $user->id, 'action' => 'profile.updated',
                'changes' => json_encode(['before' => $before, 'after' => $data]), 'created_at' => now(),
            ]);

            return $user;
        });
        $request->user()->refresh();
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Profile updated successfully.', 'display_name' => $user->display_name]);
        }

        return to_route('dashboard')->with('success', 'Profile updated successfully.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user) {
            // Use the same locking order as administrator account updates.
            $account = User::orderBy('id')->lockForUpdate()->get()->firstWhere('id', $user->id);
            abort_unless($account, 404);
            if ($account->isAdmin()) {
                throw ValidationException::withMessages([
                    'password' => 'Administrator accounts cannot be deleted. Use account management to transfer responsibilities.',
                ])->errorBag('userDeletion');
            }
            if (Document::where('created_by', $account->id)->exists()
                || DocumentRoute::where('from_user_id', $account->id)->orWhere('to_user_id', $account->id)->exists()
                || DocumentEvent::where('actor_id', $account->id)->exists()) {
                throw ValidationException::withMessages([
                    'password' => 'This account has document history and cannot be deleted. Contact an administrator for deactivation.',
                ])->errorBag('userDeletion');
            }
            $account->delete();
        }, 3);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
