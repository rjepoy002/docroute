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
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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
