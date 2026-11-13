# RTIH Task Tracker

A beginner-readable task tracker built with core PHP, MySQL/MariaDB, PDO, HTML, CSS, and JavaScript. The project implements the three assignment levels and keeps each task private to its owner.

## Requirements

- PHP 8.1 or later with `pdo_mysql` and `mbstring` enabled
- MySQL 8+ or MariaDB 10.4+
- A local PHP web server (PHP's built-in server is enough for development)
- A modern browser

This workspace did not have PHP or a MySQL/MariaDB server available during initial development. The application has since been run end to end against PHP 8.2 and MariaDB 10.11 in local Docker containers, and the manual test plan in `MANUAL_TESTS.md` was executed on 13 November 2026 with all cases passing. The screenshots with the `-live-` suffix in `screenshots/` are captures from the running application.

The interface uses a full-width task area, a date badge, status-distribution bars, responsive summary cards, clearer task actions, stronger keyboard focus indicators, and subtle card and modal transitions. Reduced-motion preferences are respected.

## Local setup

1. Install PHP and MySQL/MariaDB, and make sure PHP extensions `pdo_mysql` and `mbstring` are enabled.
2. Create the database schema by importing `database.sql`. For example, from a terminal with the MySQL client available:

   ```sh
   mysql -u root -p < database.sql
   ```

3. Copy `config/database.example.php` to `config/database.php`. Set the local host, port, database name, username, and password. `config/database.php` is excluded from Git.
4. From the project folder, start PHP's development server:

   ```sh
   php -S 127.0.0.1:8000
   ```

5. Open `http://127.0.0.1:8000/register.php` and register a demo account with your own local test email and password. There is no preloaded account, and no shared or real credential is included in this submission.
6. Sign out and back in through `login.php` to verify authentication.

The app sends clear setup/connection errors to the browser and logs database details to the PHP error log. It does not display SQL exceptions to users.

## Completed levels

- **Level 1 — Basic task management:** create, view, edit, delete, delete confirmation, and success/error feedback.
- **Level 2 — Advanced task management:** Pending / In Progress / Completed, title search, status filtering, due dates, and browser plus server-side validation.
- **Level 3 — Authentication and security:** registration and sign-in, `password_hash` / `password_verify`, session ID rotation, per-user ownership checks, PDO prepared statements, output escaping, CSRF protection, and safe error responses.
- **Task form interaction:** the “Create a task” button opens a responsive modal; editing a task opens the same modal with its current values. Close with the close button, Cancel, the backdrop, or Escape.
- **Edit/create state:** closing an edit dialog clears the edit selection and resets the form so the next new-task dialog is blank.
- **Task details:** use “View task” on a task card to open a read-only dialog with its description, status, creation date, and due date.
- **Status filter:** choosing a status immediately refreshes the task list and keeps the current title search applied.
- **Action feedback:** adding, updating, and deleting a task shows a dismissible, screen-reader-announced toast naming the task when available. Toasts automatically disappear after five seconds, including when the pointer is over them.
- **Delete confirmation:** selecting Delete opens a custom confirmation dialog with the task name, a decorative graphic, and Keep task / Delete task actions. Cancel, backdrop click, and Escape leave the task unchanged.
- **Account settings:** use the Account link in the top bar to update the sign-in email or password. Both changes require the current password; new passwords are validated and stored as password hashes.

## Roles and permissions

The application has one role: **Registered User**. A registered user can manage their own account settings and create, view, update, search, filter, and delete only their own tasks. The task owner is checked in server-side SQL for every task read or write. A visitor who is not signed in can only register or sign in; protected pages redirect them to sign-in. There is no administrator role.

## Test account

Register a local demo account at `/register.php`. Use a throwaway test email and a password of at least 8 characters.

If you imported the optional `sample_data.sql` after `database.sql`, a demo account is available:

- Email: `test.one@rtih.local`
- Password: `TestPass!123`

This is a local demo login created for verification and the delivered screenshots; it is not a real credential.

## Manual test plan and current results

The following cases were executed on **13 November 2026** against PHP 8.2 + MariaDB 10.11 running in local Docker containers, using automated browser-session requests with direct database verification. Full details are in `MANUAL_TESTS.md`. **Execution status: all 13 cases passed.**

| # | Test | Expected result | Result |
|---|---|---|---|
| 1 | Register using a valid name, email, and password | Account is created and dashboard opens | Passed (13 Nov 2026) |
| 2 | Register the same email again | Friendly duplicate-email error; no second account | Passed (13 Nov 2026) |
| 3 | Register with invalid email or short password | Inline validation; account is not created | Passed (13 Nov 2026) |
| 4 | Sign in with correct and incorrect passwords | Correct credentials open dashboard; incorrect credentials show generic error | Passed (13 Nov 2026) |
| 5 | Create a task with title, description, status, and due date | Task appears with saved values and summary counts update | Passed (13 Nov 2026) |
| 6 | Submit a blank/overlong title or invalid status/date | Server rejects the request with a helpful message | Passed (13 Nov 2026) |
| 7 | Edit a task and change status | Updated values appear; counts follow the new status | Passed (13 Nov 2026) |
| 8 | Search by part of a title and filter by status | Only matching tasks appear; clearing filters restores the list | Passed (13 Nov 2026) |
| 9 | Delete a task and cancel/accept confirmation | Cancel preserves it; accept removes it | Passed (13 Nov 2026) |
| 10 | Sign in as a second user and try another user's task URL/ID | Other user's task is inaccessible and cannot be changed/deleted | Passed (13 Nov 2026) |
| 11 | Submit a task title containing HTML/script markup | Markup displays as text; no script executes | Passed (13 Nov 2026) |
| 12 | Change/remove the CSRF token from a write request | Request is rejected with HTTP 400 | Passed (13 Nov 2026) |
| 13 | Resize the browser to mobile width | Forms, list, and dashboard remain readable without horizontal overflow | Passed (13 Nov 2026) — no horizontal overflow measured at 390 px; see `screenshots/dashboard-live-mobile.png` |

### Checks actually run in this workspace

These are limited browser/JavaScript checks using isolated HTML fixtures, not a running PHP application or database:

| Check | Result | Scope |
|---|---|---|
| JavaScript syntax check (`node --check assets/app.js`) | Passed | Static syntax only |
| View Task dialog opens and fills title, description, status, and dates | Passed | Browser fixture with representative task data |
| View dialog stays at viewport origin when its page container is transformed | Passed | Browser fixture; confirms overlay positioning |
| Close Edit, then open Create; verify blank fields, Pending status, and cleared edit URL | Passed | Browser fixture; no PHP/database involved |
| Success toast disappears after five seconds while hovered | Passed | Browser fixture; no PHP/database involved |

No registration, login, task CRUD, SQL, authorization, or real PHP/MySQL end-to-end test was run here. Those remain pending and are not reported as passed.

## Backup and restore

Use the database tools to save data (the dump includes account hashes and task records; protect it as sensitive data):

```sh
mysqldump -u root -p rtih_task_tracker > rtih_task_tracker_backup.sql
```

Restore into the configured database with:

```sh
mysql -u root -p rtih_task_tracker < rtih_task_tracker_backup.sql
```

Keep backups outside the public web root and delete them securely when no longer needed. The schema itself can be rebuilt from `database.sql`; that file contains no user records or credentials.

## Security and design notes

- All database values use PDO prepared statements; task queries also require the current session's `user_id`.
- Passwords are stored as PHP password hashes, never plaintext.
- Mutating forms use POST and a session-bound CSRF token. Logout is also a CSRF-protected POST.
- All user-controlled text rendered in HTML is escaped with `htmlspecialchars`; title search treats `%` and `_` as literal characters.
- Session cookies use `HttpOnly`, `SameSite=Lax`, and `Secure` when HTTPS is active.
- Local database credentials belong in ignored `config/database.php`, never in the repository.
- Bootstrap is omitted so the app has no third-party CSS dependency; the responsive UI is plain CSS. Google Fonts are optional remote decoration and system sans-serif fallbacks remain available offline.
- This is an internship assignment starter, not a production deployment package. It does not implement password reset, email verification, rate limiting, or an administrator role. Deploy behind HTTPS and use a least-privilege database user outside a local demo.

## AI tool disclosure and reviewed change

OpenAI Codex and ChatGPT were used to draft and review parts of this project. One reviewed security change: task list, edit, update, and delete queries include the signed-in `user_id` instead of trusting a task identifier from the browser. This makes the server enforce task ownership even if a user changes an ID in a request. The application was subsequently executed end to end against PHP 8.2 and MariaDB 10.11 in local Docker containers, and the manual test plan was run on 13 November 2026 with all cases passing (see `MANUAL_TESTS.md`). Human review before submission is still recommended.

## Short review preparation

See [REVIEW_GUIDE.md](REVIEW_GUIDE.md) for a beginner-friendly explanation of the schema, PHP request flow, validation, security decisions, likely questions, and a suggested demo sequence.
