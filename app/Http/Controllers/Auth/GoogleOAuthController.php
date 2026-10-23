<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleOAuthController extends Controller
{
    public function redirect(Request $request)
    {
        if (! filled(config('services.google.client_id')) || ! filled(config('services.google.client_secret'))) {
            return $this->failure('Google sign-in is not configured. Please use email and password.');
        }

        try {
            return Socialite::driver('google')->setScopes(['openid', 'email', 'profile'])->redirect();
        } catch (\Exception $exception) {
            return $this->failure('Google sign-in is temporarily unavailable. Please use email and password or try again.');
        }
    }

    public function link(Request $request)
    {
        // Retain the existing profile link URL and password-confirmation middleware.
        return $this->redirect($request);
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            $request->session()->forget('state');

            return $this->failure('Google sign-in was cancelled or denied. Please try again.');
        }
        if (! $request->filled('code')) {
            $request->session()->forget('state');

            return $this->failure('Google sign-in could not be completed. Please start again.');
        }

        try {
            // Never use stateless(): Socialite consumes and validates session OAuth state.
            $identity = Socialite::driver('google')->user();
        } catch (InvalidStateException $exception) {
            return $this->failure('Your Google sign-in session expired or was invalid. Please start again.');
        } catch (\Exception $exception) {
            return $this->failure('Google sign-in is temporarily unavailable. Please try again or use your password.');
        }

        $email = strtolower(trim($identity->getEmail() ?? ''));
        $googleId = $identity->getId();
        if (($identity->user['email_verified'] ?? false) !== true
            || ! filter_var($email, FILTER_VALIDATE_EMAIL)
            || ! is_string($googleId) || $googleId === '' || strlen($googleId) > 255) {
            return $this->failure('Google must provide a verified email address to sign in.');
        }

        try {
            $user = DB::transaction(function () use ($email, $googleId, $request) {
                // Serialize first linking attempts on the existing account row.
                $matches = User::whereRaw('LOWER(email) = ?', [$email])->orderBy('id')->lockForUpdate()->get();
                if ($matches->count() !== 1 || ! $matches->first()->is_active) {
                    throw ValidationException::withMessages(['email' => 'This Google account is not authorized for DocRoute. Use your approved account or contact an administrator.']);
                }
                $user = $matches->first();
                if (($request->user() && $request->user()->id !== $user->id)
                    || ($user->google_id !== null && $user->google_id !== $googleId)
                    || User::where('google_id', $googleId)->where('id', '!=', $user->id)->exists()) {
                    throw ValidationException::withMessages(['email' => 'This Google account cannot be linked. Please use your password and contact an administrator.']);
                }
                if ($user->google_id === null) {
                    $user->forceFill(['google_id' => $googleId, 'email_verified_at' => now()])->save();
                    DB::table('profile_audits')->insert([
                        'user_id' => $user->id, 'action' => 'google.linked',
                        'changes' => json_encode(['google_id' => $googleId]), 'created_at' => now(),
                    ]);
                }

                return $user;
            }, 3);
        } catch (ValidationException $exception) {
            return $this->failure($exception->errors()['email'][0]);
        } catch (QueryException $exception) {
            // The unique index is the final guard against concurrent cross-account links.
            return $this->failure('Google sign-in could not be completed. Please use your password or try again.');
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('google_name_suggestions', array_filter([
            'first_name' => is_string($identity->user['given_name'] ?? null) ? mb_substr(trim($identity->user['given_name']), 0, 100) : null,
            'last_name' => is_string($identity->user['family_name'] ?? null) ? mb_substr(trim($identity->user['family_name']), 0, 100) : null,
        ]));
        $photo = $identity->getAvatar();
        $host = is_string($photo) ? parse_url($photo, PHP_URL_HOST) : null;
        if ($host && str_ends_with($host, '.googleusercontent.com') && parse_url($photo, PHP_URL_SCHEME) === 'https') {
            $request->session()->put('google_profile_photo', $photo);
        }
        if (! $user->hasCompleteProfile()) {
            $request->session()->put('profile_suggested_name', trim($identity->getName() ?? '') ?: User::emailDisplayName($email));

            return to_route('profile.complete');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function failure(string $message)
    {
        return to_route('login')->withErrors(['email' => $message]);
    }
}
