# RTIH Task Tracker

A beginner-readable task tracker built with core PHP, MySQL/MariaDB, PDO, HTML, CSS, and JavaScript. The project implements the three assignment levels and keeps each task private to its owner.

## Requirements

| Tool | Minimum version | Download link |
|---|---|---|
| PHP | 8.1+ (with `pdo_mysql` and `mbstring` extensions enabled) | [php.net/downloads](https://www.php.net/downloads) |
| MySQL | 8.0+ | [mysql.com/downloads](https://www.mysql.com/downloads/) |
| *or* MariaDB | 10.4+ | [mariadb.org/download](https://mariadb.org/download/) |
| Git | any recent version | [git-scm.com/downloads](https://git-scm.com/downloads) |
| Web browser | any modern browser (Chrome, Firefox, Edge, Safari) | — |

> **New to PHP? Use [XAMPP](https://www.apachefriends.org/) instead.** XAMPP is a free installer that bundles PHP, MariaDB, and phpMyAdmin in one download, so Steps 1 and 2 of the installation become a single install. It works the same on Windows, macOS, and Linux.

This workspace did not have PHP or a MySQL/MariaDB server available during initial development. The application has since been run end to end against PHP 8.2 and MariaDB 10.11 in local Docker containers, and the manual test plan in `MANUAL_TESTS.md` was executed on 13 November 2026 with all cases passing. The screenshots in `screenshots/` are captures from the running application.

The interface uses a full-width task area, a date badge, status-distribution bars, responsive summary cards, clearer task actions, stronger keyboard focus indicators, and subtle card and modal transitions. Reduced-motion preferences are respected.

## Step-by-step installation (from scratch)

Follow these six steps in order. Each step explains what it does and why, so you can set the project up even if you have never run a PHP application before.

### Step 1 — Get the code

Clone the repository to your computer (or download it as a ZIP from the green **Code** button and extract it):

```sh
git clone https://github.com/Surya-007s/RTIH-Task-Tracker.git
cd RTIH-Task-Tracker
```

Everything after this step happens inside this project folder.

### Step 2 — Install and start PHP + MySQL/MariaDB

**Option A — XAMPP (easiest):**

1. Download and install XAMPP from [apachefriends.org](https://www.apachefriends.org/) with the default options.
2. Open the **XAMPP Control Panel** and click **Start** next to both **Apache** and **MySQL**. Both should turn green.

**Option B — manual install:**

1. Install PHP from [php.net](https://www.php.net/downloads) and MySQL from [mysql.com](https://www.mysql.com/downloads/) (or MariaDB from [mariadb.org](https://mariadb.org/download/)).
2. Open your `php.ini` file and make sure these two lines are **uncommented** (no `;` at the start):
   ```ini
   extension=pdo_mysql
   extension=mbstring
   ```
3. Start the MySQL/MariaDB service (on Windows it can run as a service automatically; on Linux: `sudo systemctl start mysql`).

**Check that everything is installed** — open a new terminal and run:

```sh
php -v
```
You should see PHP 8.1 or newer. If you get *"php is not recognized"*, see the Troubleshooting section below.

### Step 3 — Create the database

The file `database.sql` creates the database (`rtih_task_tracker`) and its tables (`users`, `tasks`) automatically — you do not need to create anything by hand.

**Option A — phpMyAdmin (comes with XAMPP):**

1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin) in your browser.
2. Click **Import** in the top menu.
3. Click **Choose file**, select `database.sql` from the project folder, then click **Go**.
4. You should see the database `rtih_task_tracker` appear on the left, with tables `users` and `tasks`.

**Option B — command line:**

From inside the project folder, run:

```sh
mysql -u root -p < database.sql
```

Enter your MySQL root password when asked (XAMPP's default is an empty password — just press Enter).

**Optional — demo data:** the project also ships a `sample_data.sql` file with a ready-made demo account and example tasks. Import it *after* `database.sql` if you want to try the app without creating anything yourself:

```sh
mysql -u root -p rtih_task_tracker < sample_data.sql
```

### Step 4 — Configure the database connection

The app needs to know how to connect to your database.

1. Copy the example config to the real config file:

   **Windows (Command Prompt / PowerShell):**
   ```sh
   copy config\database.example.php config\database.php
   ```
   **macOS / Linux:**
   ```sh
   cp config/database.example.php config/database.php
   ```

2. Open `config/database.php` in any text editor and set the values for **your** setup:

   ```php
   return [
       'host'     => '127.0.0.1',   // XAMPP default: leave as is
       'port'     => '3306',        // MySQL/MariaDB default port
       'database' => 'rtih_task_tracker',
       'username' => 'root',        // XAMPP default: root
       'password' => '',            // XAMPP default: empty. Use your own password otherwise
       'charset'  => 'utf8mb4',
   ];
   ```

   `config/database.php` is excluded from Git (via `.gitignore`), so your local password is never uploaded.

### Step 5 — Start the application

From inside the project folder, start PHP's built-in development server:

```sh
php -S 127.0.0.1:8000
```

You should see something like `PHP 8.x Development Server started at http://127.0.0.1:8000`. **Keep this terminal window open** — closing it stops the app.

**XAMPP-only alternative:** if you used XAMPP, you can instead copy the project folder into `C:\xampp\htdocs\` (Windows) and open `http://localhost/RTIH-Task-Tracker/register.php` — no terminal needed.

### Step 6 — Open the app and create your account

1. Open [http://127.0.0.1:8000/register.php](http://127.0.0.1:8000/register.php) in your browser.
2. Register a demo account with your own local test email and a password of **at least 8 characters**. There is no preloaded account and no shared or real credential in this submission.
3. After registering, the dashboard opens — create your first task!
4. Sign out and sign back in through [login.php](http://127.0.0.1:8000/login.php) to verify authentication works.

If you imported `sample_data.sql`, you can also sign in with the demo account:

- Email: `test.one@rtih.local`
- Password: `TestPass!123`

(This is a local demo login created for verification and the delivered screenshots; it is not a real credential.)

That's it — the app is running. The app sends clear setup/connection errors to the browser and logs database details to the PHP error log. It does not display SQL exceptions to users.

## Troubleshooting

| Problem | Likely cause | Fix |
|---|---|---|
| `php is not recognized` (Windows) | PHP is not in the system PATH | Reinstall PHP and tick "Add to PATH", or use XAMPP and run commands from the XAMPP Shell |
| `Access denied for user 'root'@'localhost'` | Wrong password in `config/database.php` | Set the correct MySQL password (XAMPP default is empty) |
| `Unknown database 'rtih_task_tracker'` | `database.sql` was not imported | Repeat Step 3 |
| `Connection refused` / MySQL errors | MySQL service is not running | Open the XAMPP Control Panel and Start MySQL (or start the service manually) |
| Blank page / missing driver error | `pdo_mysql` or `mbstring` extension is disabled | Uncomment the two extension lines in `php.ini` and restart the server (Step 2) |
| Port 8000 already in use | Another app is using the port | Run on another port: `php -S 127.0.0.1:8080` and use that URL instead |

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
| 13 | Resize the browser to mobile width | Forms, list, and dashboard remain readable without horizontal overflow | Passed (13 Nov 2026) — no horizontal overflow measured at 390 px |

### Checks actually run in this workspace

These are limited browser/JavaScript checks using isolated HTML fixtures, not a running PHP application or database:

| Check | Result | Scope |
|---|---|---|
| JavaScript syntax check (`node --check assets/app.js`) | Passed | Static syntax only |
| View Task dialog opens and fills title, description, status, and dates | Passed | Browser fixture with representative task data |
| View dialog stays at viewport origin when its page container is transformed | Passed | Browser fixture; confirms overlay positioning |
| Close Edit, then open Create; verify blank fields, Pending status, and cleared edit URL | Passed | Browser fixture; no PHP/database involved |
| Success toast disappears after five seconds while hovered | Passed | Browser fixture; no PHP/database involved |

These fixture checks were run during development using isolated HTML fixtures. They are superseded by the application-level verification above: all 13 cases were executed against a live PHP 8.2 + MariaDB 10.11 stack on 13 November 2026 and passed.

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
