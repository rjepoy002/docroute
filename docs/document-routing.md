# Document routing and tracking

Task 03 adds department-based document creation, sending, receipt, decline/return, forwarding with optional title changes, closure, search, and a chronological tracking timeline. Existing authentication, administrator management, and MySQL user/department records are preserved. Attachments, QR codes, scanning, PDFs, notifications, and reports are not implemented.

## Schema and deployment

Migration: `database/migrations/2026_10_22_100000_create_document_routing_tables.php`.

The migration was inspected, previewed with `migrate --pretend`, and applied only to the configured `docroute_v2` MySQL database. It creates four new tables; it does not alter, reset, or truncate existing user/department tables. Before and after migration, existing counts were 1 user and 1 department. No sample documents were added to MySQL.

| Table | Purpose |
| --- | --- |
| document_numbers | Durable auto-increment tracking reservations, allocation year/time |
| documents | Tracking number, reservation, current title/description, creator, single current-route pointer |
| document_routes | Sender/recipient and departments, assignment status, sent/seen/received/declined/closed timestamps and sending remarks |
| document_events | Append-only actor, event type, routing reference, old/new values, identity snapshots, remarks, timestamp |

Foreign keys restrict deletion of users, departments, documents, and routes with historical references. The profile deletion workflow presents a validation error when an account has document history. Normal document operations never delete history. Events reject Eloquent update/delete operations; no editing or deletion endpoints exist. Direct privileged SQL access is outside this application-level immutability guarantee.

### One authoritative assignment

`documents.current_route_id` points to exactly one current routing assignment after a successful send. The pointer is unique and foreign-key constrained. Its route supplies the current holder, department, status, and closing timestamp. These fields are deliberately not duplicated on documents, avoiding conflicting lifecycle state. Older routes remain historical. Service operations create the assignment and set the pointer in one transaction; submitted values cannot set the pointer or sender.

## Tracking allocation

Numbers use `DR-{Manila year}-{global reservation ID padded to at least six digits}`, for example `DR-2026-000001`. The year is dynamic in Asia/Manila. The sequence is global and does not reset at New Year; padding grows beyond six digits if needed.

`TrackingNumberAllocator` inserts an auto-increment reservation row BEFORE `DocumentWorkflow` starts the document transaction. MySQL/MariaDB allocates IDs concurrently without a COUNT or MAX calculation. The tracking number and reservation reference both have unique constraints in documents. A failed document/route/event transaction leaves its reservation behind, intentionally producing a gap. Reservations are never removed by application operations, so numbers are not reused after failed sends or document removal. Transaction retries use the same already-reserved number.

Call the public create workflow at the top level as the HTTP controller does. Do not wrap number reservation and workflow creation in a new surrounding transaction: that would undo the durable reservation guarantee. SQLite tests run inside their normal outer test transaction, but demonstrate the reservation survives rollback of the nested document transaction; durability across connection failures requires a separate MySQL test environment.

## Status transitions

| Current assignment | Action | Result |
| --- | --- | --- |
| none | create/send | Document and pending, unseen assignment; Created and Sent events |
| pending | view details by recipient | Seen timestamp/event; remains pending |
| pending | receive | Same assignment becomes received; Received event |
| pending | decline with reason | Old assignment becomes declined; new pending, unseen assignment back to the previous sender; Declined and Returned events |
| received | forward | Old assignment becomes forwarded; new pending, unseen assignment; optional Title Changed event and Forwarded event |
| received | close | Same assignment becomes closed, with closing timestamp, actor and remarks in Closed event |
| closed | any routing action | Rejected; no reopen or administrator override |

Decline returns to the immediately previous sender/forwarder, rather than always to the original creator. The return goes to that person's current active department. If that person or department is inactive, missing, or no longer matches the locked selection, the action is rejected without changing the document. An employee in a deactivated source department may still decline back to an available sender; sending/forwarding requires an active source department.

Receiving through a list action does not imply that the details were seen. Opening details marks only the current open assignment as seen and logs that once. Each new assignment starts with its own null seen timestamp; older recipients' seen timestamps remain unchanged. Closed-document viewing does not append a Seen event.

## Authorization and concurrency

- Every document endpoint requires auth, verified, and active middleware, including the existing global active-account/session checks.
- `DocumentPolicy` is discovered by Laravel's model-policy convention. Active creators and prior routing participants can view their documents. Active administrators may view all documents for organization-wide monitoring.
- All Documents is organization-wide for administrators and participant-scoped for employees. Incoming, Pending and Unseen are always scoped to the logged-in recipient, including administrators. Outgoing is always scoped to original creation.
- Routing permissions have no administrator bypass. Only the current pending recipient may receive/decline; only the current received recipient may forward/close.
- Creator and prior participants retain view access but lose action rights after reassignment. Monitoring by another administrator never marks the recipient's assignment seen.
- State changes lock the document and its current route, compare the submitted route ID, and authorize again inside the transaction. A stale form cannot act on a new assignment even when it has returned to the same employee.
- Sending/forwarding/return locks departments in ID order and relevant users in ID order, consistent with account-management lock ordering, and revalidates active state and membership. Other actions re-read and lock the acting user. Deadlocks retry up to three attempts.
- Search and pagination run after visibility restrictions. Query values use bound parameters; views escape output through Blade. All mutation forms include CSRF tokens.
- Recipient selection exposes only active users' IDs, names, and department IDs to authenticated users. Alpine filters options by department, and server validation repeats the checks.
- Event identity/department snapshots retain historical names after account or department renaming. Event ordering is created_at followed by ID, including same-second actions.

## Counter definitions

The dashboard and sidebar share one aggregate query in `DocumentOverview`; dashboard controllers supply the result to the layout to avoid repeating it. Lists and recent activity eager-load their displayed relationships.

| Counter | Definition |
| --- | --- |
| Incoming | Current pending or received assignments addressed to this user |
| Outgoing | All documents originally created by this user, regardless of current holder/status |
| Pending | Current pending assignments addressed to this user |
| Unseen | Current pending or received assignments addressed to this user with no seen timestamp |
| Completed | Closed documents created by this user OR closed by this user; each document counted once |

Recent Documents includes only documents created by or previously routed through the user, even for administrators. Organization monitoring is available through All Documents. Existing administrator user/department counts remain database-driven. Employee and administrator dashboards now use the same application layout.

Filters support tracking number/title, current assignment status, current destination department, and creation date range. Displayed times and date-filter boundaries use Asia/Manila; database timestamps remain UTC. Assignment history statuses (forwarded/declined) are shown in the timeline; the current assignment's possible states are pending, received, and closed.

## Created files

- database/migrations/2026_10_22_100000_create_document_routing_tables.php
- app/Models/Document.php
- app/Models/DocumentRoute.php
- app/Models/DocumentEvent.php
- app/Policies/DocumentPolicy.php
- app/Services/TrackingNumberAllocator.php
- app/Services/DocumentWorkflow.php
- app/Http/Requests/Documents/SendDocumentRequest.php
- app/Http/Requests/Documents/DocumentActionRequest.php
- app/Http/Controllers/DocumentController.php
- app/Http/Controllers/DashboardController.php
- resources/views/documents/create.blade.php
- resources/views/documents/destination-fields.blade.php
- resources/views/documents/index.blade.php
- resources/views/documents/actions.blade.php
- resources/views/documents/show.blade.php
- resources/views/components/document-status.blade.php
- resources/views/dashboard/document-overview.blade.php
- resources/views/dashboard/recent-documents.blade.php
- tests/Feature/DocumentWorkflowTest.php
- tests/Unit/DocumentPolicyTest.php
- docs/document-routing.md

## Modified files

- routes/web.php
- app/Support/DocumentOverview.php
- app/Http/Controllers/Admin/DashboardController.php
- app/Http/Controllers/ProfileController.php
- resources/views/layouts/docroute.blade.php
- resources/views/admin/dashboard.blade.php
- resources/views/dashboard.blade.php
- public/build/manifest.json and generated frontend assets

Local, private verification artifacts are under `storage/app/docroute-smoke/`; these are not application features or production fixtures.

## Verification

Executed:

- php artisan migrate --pretend --path=database/migrations/2026_10_22_100000_create_document_routing_tables.php
- Guarded Artisan migrate invocation for that exact migration after confirming the configured database name was docroute_v2
- php artisan test: 88 tests passed, 553 assertions (including the existing suites)
- php -l on all new/changed PHP files
- php artisan route:list
- php artisan view:cache
- npm.cmd run build
- Laravel Pint on the new/changed PHP files

A headless Chrome smoke check used real login/logout and form submissions against a separate disposable SQLite database. It passed create, receive, forward with title change, decline/return, receive again, close, and organization monitoring. Department-driven recipient options, mobile sidebar open/close, and page overflow were checked. No JavaScript errors or horizontal page overflow were detected. Desktop (1440px) and mobile (390px) screenshots were captured; no original MySQL test accounts or documents were created.

## Manual browser checklist

1. Sign in as an active user assigned to an active department. Confirm Dashboard, Create Document, Incoming, Outgoing, Pending, Unseen, and All Documents work. Confirm administrator Users/Departments links still work.
2. Create a document with a title and optional description/remarks. Change departments and confirm recipient choices update. Check that mismatched or inactive recipients cannot be submitted successfully.
3. Use separate browser profiles for sender, recipient, next recipient and administrator. Confirm the recipient sees an unseen pending assignment and the original sender sees it under Outgoing.
4. Open details as the sender and monitoring administrator. Confirm the recipient's Unseen count stays unchanged. Open as the recipient: Unseen decreases, while Pending remains unchanged.
5. Receive from the list/details. Confirm Pending decreases, status becomes Received, and Forward/Close become available without closing automatically.
6. Forward with remarks and an optional new title. Confirm the tracking number stays unchanged, the new recipient sees a new unseen pending assignment, and the timeline preserves old/new titles and previous participants.
7. Decline as the new pending recipient with a required reason. Confirm it returns to the immediately previous forwarder as a new pending assignment. Try while the previous sender/department is inactive; verify clear rejection and no partial changes.
8. Receive and close with optional remarks. Confirm the closing actor/time, completed count, and rejection of later routing actions. Try stale forms from another tab and verify no duplicate actions occur.
9. Search/filter/paginate by tracking number/title, status, current department and creation date range. Confirm an unrelated employee cannot find/open the document by changing URLs or filters.
10. Verify the timeline remains in order when events share timestamps. Rename a test account/department and confirm earlier event snapshots retain the original names.
11. Check desktop, tablet and smartphone widths, long titles, table scrolling, mobile sidebar overlay/close/Escape, confirmations and touch controls.
12. Recheck admin management, inactive-account logout, password reset/session revocation, regular login/logout, and disabled public registration. Use deactivation to retire test accounts with document history.

## Known limitations and follow-up

- SQLite verifies behavior and stale request rejection, but cannot reproduce InnoDB row-lock contention, deadlock handling, or multi-process tracking allocation. The migration was exercised on local MySQL; parallel business transactions were not tested there to avoid adding data. Next, run simultaneous create and receive/decline/forward requests against a dedicated disposable MySQL database and assert unique numbers, one current assignment, and an unbroken audit trail.
- Tracking reservations must not be purged or reset. Deployments should back up them together with document data and preserve sequence high-water marks when restoring.
- Department locks are deliberately broad for consistency with existing account management; large deployments may narrow the locking strategy after measuring contention.
- Recipient choices and full document timelines load as a whole. Very large organizations or long histories may need a paginated authorized recipient endpoint and paginated timeline.
- The existing User model still does not implement MustVerifyEmail. Required verified middleware remains present; this phase does not change existing email-verification enforcement.
- JavaScript is required for the dependent recipient selector and mobile sidebar. Browser smoke checks covered Chrome; other browsers and tablet interactions remain useful manual checks.
- No unrestricted admin override or document reopening exists. If an assignment is held by an unavailable employee, an explicit, separately authorized and audited reassignment policy is a future task.
