> Google login policy update: see [google-login-setup.md](google-login-setup.md). Google now links only existing active administrator-provisioned accounts on first verified login. Company-domain self-onboarding and mandatory password-first linking described below are superseded. OAuth route names are now `auth.google.redirect` and `auth.google.callback`.

# Employee profile completion

Run `composer install`, then `php artisan migrate` to install the additive profile columns and profile audit table. No existing employee or document rows are removed or automatically marked complete. Existing users supply their designation and department on their next sign-in.

Configure `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and `GOOGLE_REDIRECT_URI` using a Google OAuth web application. Register the exact callback URL, including the application's deployment prefix, in Google's authorized redirect URIs. Google sign-in uses Laravel Socialite's session state validation. See [Google's web-server OAuth documentation](https://developers.google.com/identity/protocols/oauth2/web-server).

`ONBOARDING_COMPANY_DOMAINS` is a comma-separated list of exact permitted company email domains. Leave it empty to disable new Google accounts. Personal gmail.com and googlemail.com accounts cannot self-register, even if mistakenly listed. Public password registration remains disabled.

Existing employees sign in with their password and use **Profile → Link your Google account**, with password confirmation, before using Google sign-in. A verified matching email alone never links an existing account. Google identity links are unique, require the same email, and cannot be replaced through profile submission.

All profiles require a full name, active department, and designation; contact number is optional. The email is read-only. Successful submission records a profile audit and immediately redirects to the dashboard, without approval. Existing roles and activation states are preserved; Google onboarding assigns only `employee`.

`profile_audits` retains profile creation, completion, update, and Google-link events. It contains employee information and should follow the organization's access and retention controls.

Tests use an isolated in-memory SQLite database. Google identity responses are mocked; a live OAuth round trip requires deployment credentials.
