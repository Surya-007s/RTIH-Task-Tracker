# Delivery notes — RTIH Task Tracker

This folder is assembled to match the deliverables list in the assignment PDF (Section 6).
Each item below points to where it is fulfilled.

## Deliverables checklist

| # | Required deliverable (assignment Section 6) | Where |
|---|---|---|
| 1 | Complete source code | This folder: `dashboard.php`, `login.php`, `register.php`, `account.php`, `logout.php`, `config/`, `assets/` |
| 2 | SQL export with table creation statements (+ optional sample data) | `database.sql` (schema, imports cleanly) and `sample_data.sql` (optional demo account + three tasks matching the screenshots) |
| 3 | README with setup instructions, PHP/MySQL configuration steps, test login details, and the roles/permissions explanation | `README.md` — setup steps under "Local setup"; test login below; role model under "Security and design notes" |
| 4 | Screenshots showing the application working | `screenshots/*.png` — captured 13-14 Nov 2026 from the running app (PHP 8.2 + MariaDB 10.11 in Docker) with live task data |
| 5 | Note of completed levels and known limitations | `COMPLETED_LEVELS.md` and the "Completed levels" / "Security and design notes" sections of `README.md` |
| 6 | Test cases run, with results, and bugs found and fixed | `MANUAL_TESTS.md` — all 13 cases executed 13 Nov 2026, results recorded, plus the browser-fixture checks and the bug/fix table |
| 7 | Note on database backup and restore | `README.md` — "Backup and restore" section |
| 8 | AI coding assistant disclosure with one reviewed change | `README.md` — "AI tool disclosure and reviewed change" section |

## Test login details

After importing `database.sql` (and optionally `sample_data.sql`):

- Email: `test.one@rtih.local`
- Password: `TestPass!123`

This is a throwaway local demo account created for verification and the delivered
screenshots; its bcrypt hash ships in `sample_data.sql`. Any new account can also be
registered at `/register.php`.

## Roles and permissions (short)

One role: authenticated user. Each signed-in user can view, create, update, and delete only
their own tasks — every task SELECT/UPDATE/DELETE in the backend includes the session's
`user_id` in the SQL condition, so a modified URL or request ID cannot reach another user's
data. Full discussion in `README.md`.

## Deliberately not included (per assignment Section 10)

- `config/database.php` — contains local database credentials. Copy
  `config/database.example.php` to `config/database.php` during setup (steps in `README.md`).
- The Git metadata folder (`.git`).

## Environment used for verification

PHP 8.2 (built-in development server) and MariaDB 10.11.19, running as two Docker containers
on Windows 11. All 13 manual test cases passed on 13 November 2026 (see `MANUAL_TESTS.md`).
