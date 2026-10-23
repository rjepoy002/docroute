# Authentication UI modernization

The existing Laravel 12 / Blade / Tailwind 3 / Vite / Alpine stack is retained. No authentication controllers, permissions, database records, or document routing logic were changed for this UI update.

## Files

Created: `resources/views/components/auth-password.blade.php`, `public/images/README.txt`, this report, `tests/Feature/Auth/AuthenticationUiTest.php`, and screenshots under `docs/screenshots/`.

Modified: `resources/views/layouts/guest.blade.php`; the login, forgot-password, and reset-password views under `resources/views/auth/`; `resources/views/profile/complete.blade.php`; `resources/views/profile/fields.blade.php`; `resources/css/app.css`; `resources/js/app.js`.

CSS adds the responsive navy/green authentication shell, advisory, form controls, focus states, native dialog styling, and reduced-motion support. Authentication styling is scoped to dedicated classes. JavaScript reuses Alpine for password visibility and adds submit feedback, back-navigation recovery, and native modal activation/focus containment. Browser validation still precedes submission; no extra frontend dependencies were added to the project.

## Setup

Place the authentic supplied logo at `public/images/paleco-logo.png`. No logo was supplied in the project or request attachment, so the page currently displays text branding. The image is shown automatically when present, with its proportions preserved.

Google Sign-In remains conditional on configured `GOOGLE_CLIENT_ID`, with the existing OAuth configuration and company-domain eligibility policy retained. Until configured, no nonfunctional Google button is shown. Follow `docs/employee-profile-completion.md` for OAuth setup. The email advisory neither assumes a domain nor changes access policy.

Run `npm.cmd run build` after frontend edits on Windows. The production build has been generated. No database migration is needed for this UI change.

## Routes reused

`login`, `google.redirect`, `password.request`, `password.email`, `password.store`, `profile.complete.store`, and `logout`. All existing CSRF tokens, field names, reset tokens, OAuth state validation, rate limiting, and dashboard redirects are retained.

## Verification

- Production Vite build succeeded.
- Existing Laravel suite: 104 tests, 646 assertions passed before adding UI route checks.
- Headless Microsoft Edge: 320, 375, 768, 1024, and 1440 pixel widths, no horizontal overflow; show/hide password works at each width.
- Forgot/reset pages render in the browser.
- Browser checks confirm submit loading/disabled feedback and restoration after back navigation. Submission was intercepted without posting credentials or modifying records.
- Screenshots: `docs/screenshots/login-375.png`, `login-1440.png`, and `forgot-password-1440.png`. Logo and Google button are absent because those deployment settings are not supplied.
- Existing profile completion, roles, eligibility, password recovery, dashboard, and document routing behavior is covered by the Laravel suite. A live Google round trip and official logo appearance require the supplied asset and OAuth credentials.

No database reset or GitHub push was performed.

Final Laravel verification: 107 tests passed, 660 assertions, including configured Google OAuth initiation and UI route checks.
