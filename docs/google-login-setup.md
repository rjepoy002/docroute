# Google login setup

## Current policy

Only an existing active DocRoute account may sign in with Google. This application uses administrator-provisioned users and `is_active` as its existing admission controls; there is no separate approval flag. Public registration remains disabled. Google sign-in never creates users or departments and never changes roles, departments, permissions, passwords, or saved names. The company-domain onboarding configuration is no longer used by Google login.

On the first successful state-validated Google callback, a verified email matching exactly one existing active account (case-insensitively) may link its Google subject ID. This supersedes the earlier password-first linking policy. Previously linked accounts must match both subject ID and email. Different linked subjects, an identity owned by another account, and attempts to switch an already authenticated session to another account are rejected. Row locking, a transaction, and the existing unique `google_id` index protect linking; new links are audited in `profile_audits`.

Incomplete profiles still open the existing completion dialog, with an editable Google display-name suggestion or email-name fallback. Complete profiles return to the intended application destination, defaulting to the dashboard. Existing authorization middleware still protects that destination. Email/password sign-in and logout remain available.

## Environment

The existing `.env` values and secrets were preserved. Required values:

```dotenv
APP_URL=http://localhost/docroute-v2
GOOGLE_CLIENT_ID=<your web application client ID>
GOOGLE_CLIENT_SECRET=<your web application client secret>
GOOGLE_REDIRECT_URI=http://localhost/docroute-v2/auth/google/callback
```

Never commit the real credentials. After editing configuration, run `php artisan config:clear`. If using a deployment configuration cache, regenerate it afterwards.

Socialite was already installed and compatible with Laravel 12. The existing profile migration already provides nullable unique `users.google_id`; no duplicate column or new migration is needed. `php artisan migrate --no-interaction` reported nothing to migrate.

## Google Cloud

Use the existing OAuth **Web application** client. Register this exact authorized redirect URI:

`http://localhost/docroute-v2/auth/google/callback`

When the consent application uses Testing status, add each permitted testing Google account to its test-user list where Google requires it. Test users must also have an existing active administrator-provisioned DocRoute account with the same email. Google Cloud test-user membership alone does not grant application access. Internal organization consent restrictions may also apply.

Only `openid email profile` scopes are requested. Socialite uses the web session to validate and consume OAuth state; `stateless()` is never used. Access and refresh tokens are not stored.

## Routes and XAMPP

- `auth.google.redirect`: `/auth/google`
- `auth.google.callback`: `/auth/google/callback`
- Existing `/auth/google/link` remains available through `google.link`, with authentication/password confirmation.

The route names changed from `google.redirect` and `google.callback`; internal view and test references were updated. The public URL paths are unchanged.

The existing root `index.php` delegates to `public/index.php`. The root `.htaccess` handles the `/docroute-v2` prefix, protects project directories, and forwards application routes to the front controller. These files were inspected and retained. No domain-root or `/public` callback is assumed. CLI route generation and the actual Apache OAuth redirect both produced the exact configured callback above. Google credentials and OAuth query strings were not printed during verification.

If Apache returns 404 before reaching Laravel, verify `mod_rewrite`, the applicable `AllowOverride` setting, and the project's existing `.htaccess`. Do not expose project source directories to resolve a routing issue. For a public-only Apache Alias, retain the same external `/docroute-v2` prefix and register the URL actually served.

## Manual verification

1. Open `http://localhost/docroute-v2/login` and select **Continue with Google**. The button appears when the Google client ID is configured.
2. Choose a Google account matching an existing active employee. Confirm consent and verify successful sign-in, profile completion when required, and the intended authorized page.
3. Verify that the first login sets `google_id`, records `google.linked`, and leaves the role and department assignment unchanged. Repeat login to confirm no relinking or duplicate link audit.
4. Cancel consent and verify a safe message on the login page. Try an unknown email or inactive DocRoute account and confirm access is refused without creating a user.
5. Sign out and verify email/password sign-in, password recovery, and document routing still work.

A complete interactive Google consent round trip must be performed manually using the configured Google account. Automated tests mock Google responses and also exercise Socialite's real state validation and replay rejection.

## Troubleshooting

- `redirect_uri_mismatch`: compare the Google Cloud redirect URI, `.env`, and outgoing OAuth `redirect_uri` character for character, including scheme, host, path prefix, and trailing slash.
- Expired/invalid state: start again from the login button in the same browser. Do not mix `localhost` and `127.0.0.1`. Check session storage and cookie domain/path. Local HTTP must not require secure-only cookies.
- Unauthorized account: confirm that an administrator created/activated the account and that its email matches Google's verified address. Domain membership alone is insufficient.
- Link conflict: use password sign-in and have an administrator investigate. The callback never silently replaces an existing Google identity.
- Google service/configuration errors: use email/password temporarily. Check credentials and Google consent settings without exposing credentials or tokens to users.

## Production

Use HTTPS for the application and Google callback, register the exact production HTTPS URL, configure secure session cookies and trusted proxies as appropriate, protect `.env`, and serve only the public directory. Use separate production credentials where appropriate. HTTP localhost is for local development.

## Verification and changed files

- 121 Laravel tests passed, 794 assertions.
- `npm.cmd run build` succeeded.
- Apache login: HTTP 200; Google authorization: HTTP 302 to `accounts.google.com`, exact callback, three scopes, session state present; denied callback: HTTP 302 back to the prefixed login URL.
- No database reset, truncation, account creation, department seeding, or GitHub push was performed.

Created `app/Http/Controllers/Auth/GoogleOAuthController.php`, `tests/Feature/Auth/GoogleOAuthTest.php`, and this document. Replaced the earlier `GoogleController.php`. Updated `routes/auth.php`, `routes/web.php`, `config/services.php`, `resources/views/auth/login.blade.php`, `tests/Feature/Auth/AuthenticationUiTest.php`, `tests/Feature/Auth/ProfileCompletionTest.php`, and the earlier authentication documentation to identify the superseded policy.