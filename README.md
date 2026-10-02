# DocuRoute V2

DocuRoute is a lightweight PHP and MariaDB document-routing application for XAMPP. V2 preserves the legacy create, receive, forward, decline, close, search, account, and routing-history workflows while centralizing configuration, authentication, layout, and workflow actions.

## Local setup

1. Place the project in `C:\xampp\htdocs\docuroute`.
2. Import the existing DocuRoute data into `docrxzp_pal_db`.
3. Apply [001_v2_schema.sql](database/migrations/001_v2_schema.sql) once after a database backup.
4. Start Apache and MariaDB in XAMPP, then open `http://localhost/docroute/`.

The default development connection is configured in `config/database.php`. Override it with `DOCROUTE_DB_HOST`, `DOCROUTE_DB_USER`, `DOCROUTE_DB_PASSWORD`, and `DOCROUTE_DB_NAME` environment variables when appropriate.

## Requirements

- PHP 7.4+ with `mysqli` enabled (PHP 8.1+ recommended)
- MariaDB/MySQL and Apache through XAMPP
- Database: `docrxzp_pal_db`

Review the diagnostics in `database/migrations/001_v2_schema.sql` before applying any schema change. The migration intentionally does not make unsafe assumptions about columns, indexes, or duplicate tracking numbers.

## Structure

- `config/` — app and database configuration
- `includes/` — authentication, CSRF, helpers, flash messages, and shared layout
- `auth/` — account-facing pages
- `documents/` and `actions/` — routing UI and protected POST operations
- `admin/` — administrator-only account management
- `assets/` — responsive CSS and JavaScript
- `database/migrations/` — explicit schema changes

## Security behavior

The authenticated user is stored in the server session; URL parameters never establish identity. State changes require CSRF tokens, authorization checks, prepared statements, and POST/Redirect/Get. Plaintext legacy passwords are upgraded automatically to secure hashes after a successful login.

## Workflow

Create and route a document → recipient receives or declines it → current holder forwards it or closes it. Every event is retained in `dr_logs` and displayed in document history.

## Grouped dashboard behavior

Legacy `main2.php` and its `_i`, `_r`, and `_o` drill-down pages were a grouped-dashboard presentation, controlled by `dr_users.isgroup`. They grouped incoming and receivable documents by sender and outgoing documents by receiver. V2 preserves that preference in `dashboard.php`: grouped users see contact summaries and can drill into the corresponding document list; normal users see per-document sections directly.

## Development branch

The V2 reconstruction and stabilization work is maintained on `refactor/docuroute-v2` until it has passed the manual XAMPP test checklist. It must not be merged into `main` before that verification.
