# Confirmed PALECO departments

Applied October 23, 2026, using `php artisan db:seed --class=LegacyDocRouteDepartmentsSeeder --no-interaction`.

ICT is a division under Corporate Planning Department. The existing ICT row (ID 1) was converted in place to CPD, preserving its assigned user and all foreign keys. No separate division table was introduced. Eight additional departments were inserted. No departments or users were deleted, and no tables were reset or truncated.

The seeder matches by unique code, corrects official names, activates all nine confirmed departments, and runs in a transaction. It was executed twice to verify repeatability. If both ICT and CPD exist on another installation, it aborts without changes rather than silently merging assignments. Unrelated department rows are preserved.

Verified local database: exactly nine departments, nine distinct codes, all confirmed names and active states, unchanged user assignment IDs and document route references. A pre-change snapshot is stored on the local storage disk at `department-master-before.json`.

`php artisan migrate --no-interaction` reported nothing to migrate. Existing unique code constraints and ID-based foreign keys are retained. Existing administrator authorization still protects department creation, editing, activation, and deactivation.

Files created:
- `database/seeders/LegacyDocRouteDepartmentsSeeder.php`
- `tests/Feature/DepartmentMasterDataTest.php`
- `docs/department-master-data.md`

Files modified:
- `app/Models/Department.php`: reusable `display_name` accessor.
- `app/Http/Controllers/DocumentController.php`: include department codes in database projections.
- `resources/views/profile/fields.blade.php`
- `resources/views/admin/users/form.blade.php`
- `resources/views/admin/users/index.blade.php`
- `resources/views/admin/departments/index.blade.php`
- `resources/views/documents/destination-fields.blade.php`
- `resources/views/documents/index.blade.php`
- `resources/views/documents/show.blade.php`

All listed selectors load departments dynamically and submit department IDs; labels display code, en dash, and official name. Create and Forward use the shared destination selector. Historical document event text remains unchanged.

Verification: 111 Laravel tests passed (702 assertions), including master data correctness, idempotency, ICT conversion with preserved user/document references, dropdown labels, forwarding form, and administrator-only management. Frontend production build passed.
