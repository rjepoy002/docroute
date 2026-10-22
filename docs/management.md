# Department and employee account management

Implemented in the existing DocRoute v2 workspace. No migrations, database resets, production data edits, or new packages were needed.

## Behavior

- Administrators can search, paginate, create, edit, activate, and deactivate departments and accounts.
- Department codes are normalized to uppercase. Unique department names/codes and user emails are validated against the database's existing unique indexes.
- New accounts require an active department. Existing assignments to subsequently deactivated departments can remain while editing an account; reassignment to an inactive department is rejected.
- Department counts include all assigned accounts, including administrators. Deactivation retains assignments and records. No management delete routes exist.
- All management routes use auth, verified, active, and admin middleware. Validation requests also authorize administrators.
- Inactive users cannot log in. Every web request checks an authenticated account's active status and invalidates inactive sessions.
- Account updates lock the department, then user rows in ID order, and recheck the acting administrator inside a transaction. Self-demotion and self-deactivation are rejected. The last active administrator is protected. Deadlock retries are enabled.
- Profile deletion also uses user-row locks and prohibits administrator deletion, closing the alternate deletion path. Employee profile deletion remains available through Breeze.
- Password resets require the acting administrator's current password, a confirmed new password with at least 12 characters including mixed case, numbers and symbols, and a rate-limited POST form with a PUT method override. Password hashes and entered passwords are never rendered or flashed.
- Password resets revoke reset tokens and remember tokens, delete database sessions for the target account, and enable ongoing password-hash checks through AuthenticateSession.
- Public registration remains disabled.

## File inventory

Created:

- app/Http/Controllers/Admin/DepartmentController.php
- app/Http/Controllers/Admin/UserController.php
- app/Http/Requests/Admin/DepartmentRequest.php
- app/Http/Requests/Admin/UserRequest.php
- app/Services/ManageAccounts.php
- resources/views/admin/departments/index.blade.php
- resources/views/admin/departments/form.blade.php
- resources/views/admin/users/index.blade.php
- resources/views/admin/users/form.blade.php
- resources/views/admin/users/password-fields.blade.php
- resources/views/components/management-heading.blade.php
- resources/views/components/status-badge.blade.php
- tests/Feature/ManagementTest.php
- docs/management.md

Modified:

- routes/web.php
- bootstrap/app.php
- app/Http/Requests/Auth/LoginRequest.php
- app/Http/Controllers/ProfileController.php
- database/factories/UserFactory.php
- resources/views/layouts/docroute.blade.php
- public/build/manifest.json and generated frontend assets

## Verification commands

- php artisan test (in-memory SQLite, enforced by existing phpunit.xml)
- php -l for all changed PHP source/test files
- php artisan route:list
- php artisan route:list -vv --path=admin
- php artisan view:cache
- npm.cmd run build

## Manual browser testing

1. Sign in with the existing administrator and use Departments and Users in the sidebar. Confirm the dashboard remains accessible.
2. Create a temporary test department and account; verify search, pagination when enough records exist, validation messages, confirmations and status badges.
3. Try a duplicate department code/name and email; confirm the form rejects them.
4. Deactivate the temporary department. Check that existing assignments remain and new assignments are rejected. Reactivate it.
5. Edit the temporary account's name, department, role and status. In a separate browser session, confirm deactivation prevents subsequent authenticated requests and login.
6. Try deactivating or demoting your own administrator; confirm rejection. Do not change the original administrator or ICT department while experimenting.
7. Reset only the temporary account password. Check administrator-password confirmation, password confirmation and complexity validation, and logout of the target's existing session. Verify the new password works and the old one fails. Share passwords privately.
8. Sign in as the temporary employee; confirm direct requests to /admin/users and /admin/departments return 403. Confirm /register remains unavailable.
9. Check forms, tables, horizontal table scrolling, sidebar, overlay, close controls and logout at desktop and mobile widths. Use deactivation rather than deletion to retire temporary records.

## Limits and follow-up

- Browser automation was unavailable; visual checks remain manual.
- SQLite tests exercise safeguards and stale authorization but cannot verify simultaneous MySQL row-lock contention. Concurrency protection uses transactional InnoDB row locks; a dedicated disposable MySQL test database is needed for a true parallel integration test.
- Account writes lock user rows broadly for consistency. A larger deployment may benefit from a dedicated serialization lock.
- The existing User model does not implement MustVerifyEmail. The verified middleware remains present; email-verification enforcement was not introduced in this phase.
- Administrators choose passwords and share them privately. Mandatory password change on first login and invitation emails are not implemented.
- Document routing and other later-phase modules remain out of scope.
