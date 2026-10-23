<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(Request $request)
    {
        $request->session()->forget('google_link_user');

        return Socialite::driver('google')->redirect();
    }

    public function link(Request $request)
    {
        $request->session()->put('google_link_user', $request->user()->id);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        $linkId = $request->session()->pull('google_link_user');
        try {
            // Socialite validates the single-use OAuth state against this session.
            $identity = Socialite::driver('google')->user();
        } catch (\Exception $exception) {
            return to_route('login')->withErrors(['email' => 'Google sign-in failed. Please try again.']);
        }
        $email = strtolower($identity->getEmail() ?? '');
        abort_unless(($identity->user['email_verified'] ?? false) === true
            && filter_var($email, FILTER_VALIDATE_EMAIL)
            && is_string($identity->getId()) && $identity->getId() !== '', 403);

        $user = DB::transaction(function () use ($identity, $email, $linkId, $request) {
            $user = User::where('google_id', $identity->getId())->lockForUpdate()->first();
            if ($linkId) {
                abort_unless($request->user()?->id === $linkId, 403);
                abort_if($user && $user->id !== $linkId, 403);
                $user = User::whereKey($linkId)->lockForUpdate()->firstOrFail();
                abort_unless($user->is_active && strtolower($user->email) === $email, 403);
                abort_if($user->google_id && $user->google_id !== $identity->getId(), 403);
                $user->forceFill(['google_id' => $identity->getId(), 'email_verified_at' => now()])->save();
                $this->audit($user, 'google.linked');
            } elseif (! $user) {
                // Never link by email. Existing employees must first sign in with their password.
                abort_if(User::where('email', $email)->exists(), 403, 'Sign in with your password and link Google from your profile.');
                $domain = explode('@', $email)[1];
                abort_unless(! in_array($domain, ['gmail.com', 'googlemail.com'], true)
                    && in_array($domain, config('onboarding.company_domains', []), true), 403);
                $user = new User([
                    'name' => trim($identity->getName() ?? '') ?: User::emailDisplayName($email),
                    'email' => $email, 'password' => Str::random(64),
                    'role' => 'employee', 'is_active' => true,
                ]);
                $user->forceFill(['google_id' => $identity->getId(), 'email_verified_at' => now()])->save();
                $this->audit($user, 'profile.created');
            }
            abort_unless($user->is_active && strtolower($user->email) === $email, 403);
            if (! $user->hasCompleteProfile()) {
                $user->name = trim($identity->getName() ?? '') ?: User::emailDisplayName($email);
            }

            return $user;
        });
        Auth::login($user);
        $request->session()->regenerate();
        // Preserve Google's suggested name without saving over an employee's edited profile.
        $request->session()->put('profile_suggested_name', $user->name);

        return to_route($user->hasCompleteProfile() ? 'dashboard' : 'profile.complete');
    }

    private function audit(User $user, string $action): void
    {
        DB::table('profile_audits')->insert([
            'user_id' => $user->id, 'action' => $action,
            'changes' => json_encode(['google_id' => $user->google_id]), 'created_at' => now(),
        ]);
    }
}
