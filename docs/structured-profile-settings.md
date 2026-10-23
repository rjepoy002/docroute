# Structured profile names and modal settings

## Result

The additive migration `2026_10_23_120000_add_structured_names_to_users.php` adds nullable `first_name` (100), `last_name` (100), and `middle_initial` (5). It has been applied locally. Deployment command: `php artisan migrate --no-interaction`.

Existing `name` values are preserved in storage. The centralized `User::display_name` formatter uses confirmed structured names, formats an optional uppercase initial with one period, and falls back to the legacy name when either required structured field is missing. `$user->name` delegates to the same formatter, so existing live user displays and recipient data use it without replacing user ID relationships. Historical document events and snapshots are not rewritten.

No backfill is performed: compound names cannot safely be inferred from full names. Existing completed accounts remain accessible without being forced to re-complete their profiles. Saving Profile Settings requires first and last names. Completion of an incomplete profile also requires structured names.

The existing Google policy remains unchanged: only existing active authorized accounts may sign in; public registration is not enabled. For first login/linking, verified Google `given_name` and `family_name` are session suggestions only. Missing structured Google data leaves fields blank for confirmation. Existing nonempty manual fields win over suggestions, and no middle initial is inferred. This deliberately preserves the authorization policy rather than implementing the request's hypothetical new-user registration scenario. Existing unknown-user rejection tests remain active.

Google avatar URLs are accepted only over HTTPS from Googleusercontent subdomains and kept in the session; otherwise an initial avatar is used. No local password is required by the profile settings modal or normal Google login.

## Settings behavior

The dashboard profile dropdown and sidebar open a native dialog with responsive fields, read-only email/department/role, account connection indicator, Cancel, Save Changes, and close controls. Native dialog behavior contains focus. Escape and Cancel protect unsaved changes; closing restores focus to the profile trigger. Saving uses an authenticated CSRF-protected JSON request, prevents duplicate submissions, preserves field values on failure, and shows inline errors. Success updates current-user names in the dropdown, sidebar, and welcome heading, shows feedback, and closes the dialog.

`GET /profile` remains compatible by redirecting to `dashboard?profile=settings`. The query is a fixed modal state; no arbitrary destination is accepted. Existing account deletion and password endpoints remain registered. The old full-page profile templates remain in the repository for compatibility but are no longer served by `/profile`.

The update endpoint accepts only first name, last name, and middle initial. It rejects supplied user IDs and authorization fields, locks the authenticated account while saving, and audits changes. Initials accept a single ASCII alphabetic character and optional period, normalize to uppercase without a stored period, and may be blank. Names are trimmed and whitespace-only required fields fail validation. Department changes through settings are prohibited; completion also cannot replace an existing department assignment.

## Files created

- `database/migrations/2026_10_23_120000_add_structured_names_to_users.php`
- `resources/views/profile/settings-modal.blade.php`
- `resources/js/profile-settings.js`
- `tests/Feature/StructuredProfileTest.php`
- `docs/structured-profile-settings.md`

## Files modified for this enhancement

- `app/Models/User.php`
- `app/Http/Requests/ProfileUpdateRequest.php`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Controllers/ProfileCompletionController.php`
- `app/Http/Controllers/Auth/GoogleOAuthController.php`
- `resources/views/profile/fields.blade.php`
- `resources/views/layouts/docroute.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/css/app.css`
- `resources/js/app.js`
- `tests/Feature/ProfileTest.php`
- `tests/Feature/Auth/ProfileCompletionTest.php`
- `tests/Feature/DepartmentMasterDataTest.php`

## Verification

- Laravel suite: 130 tests passed, 890 assertions.
- Production frontend build passed.
- Additive migration applied successfully; no reseeding, reset, or truncation.
- Headless Microsoft Edge checked modal sizing without horizontal overflow at 320, 375, 768, and 1440 pixels; initial focus; Escape closing; unsaved-change protection; inline JSON validation retaining input; and successful save closing/updating the dropdown. The browser preview used a temporary synthetic employee and intercepted update responses, without modifying employee records. Backend persistence and authorization were verified separately through the isolated Laravel test database.
- Tests cover name normalization and limits, empty initials, legacy login, guest denial, crafted permission/ID changes, legacy route compatibility, Google suggestion/manual-name behavior, and unchanged document references and history.

No extra configuration or name backfill is required. Employees confirm their own structured names through Profile Settings. Google profile photos become available after a successful Google login. A live Google consent flow still requires an interactive check using the existing credentials.