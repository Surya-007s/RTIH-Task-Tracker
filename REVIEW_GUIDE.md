# Short technical review guide

Use this as a speaking outline, not a script. Be ready to point to the matching code and describe what you personally understand.

## 60-second project overview

“RTIH Task Tracker is a core PHP application backed by MySQL or MariaDB through PDO. A registered user can manage their own tasks, search by title, filter by status, and update their account details. I chose a small schema with a users table and a tasks table. The task owner is checked in server-side queries, and the interface uses ordinary HTML forms so the request flow is easy to follow.”

## Database structure

- **`users`** has an auto-incrementing ID, display name, unique email, password hash, and account creation timestamp.
- **`tasks`** has its own ID plus `user_id`, title, description, status, optional due date, creation timestamp, and automatically updated timestamp.
- `tasks.user_id` references `users.id`. The foreign key cascades task deletion if its owning user is removed.
- The `(user_id, status, due_date)` index helps common owner/status/date list queries. The unique email index prevents duplicate accounts.
- `utf8mb4` supports full Unicode text. The optional `sample_data.sql` seeds one demo account (`test.one@rtih.local`) with a documented local demo password.

**Why these choices?** Each task belongs to exactly one user, so a foreign key records that relationship and prevents orphaned tasks. The status is constrained to the three supported values. `due_date` is nullable because it is optional.

## PHP request flow

1. Pages include `config/bootstrap.php`, which loads local configuration, creates the PDO connection, and starts the session.
2. Protected pages call `require_login()` to redirect guests.
3. Forms submit with GET for searching/filtering and POST for state changes.
4. The dashboard validates submitted task fields, prepares an SQL statement, binds values, and executes it.
5. Successful writes store a short session flash message and redirect back to the dashboard (Post/Redirect/Get), so refreshing does not resubmit the form.
6. `config/header.php` and `config/footer.php` provide the shared page frame; `assets/app.js` handles dialog and toast interactions.

## Validation decisions

- The browser provides helpful input types, required fields, and maximum lengths, but those checks can be bypassed.
- PHP repeats important checks on the server: title required and at most 120 characters; description at most 2,000; status must be one of the allowed values; due date must parse exactly as `YYYY-MM-DD`.
- Registration checks name/email lengths, email format, and password length. Account changes require the current password; new password and confirmation must match.
- Email uniqueness is enforced in both the database and handled as a friendly duplicate-email response.

## Security and authorization

- **Passwords:** `password_hash(..., PASSWORD_DEFAULT)` stores a one-way hash; `password_verify()` checks sign-in and current-password confirmation. Plaintext passwords are never stored.
- **SQL injection:** values are passed using PDO prepared statements, with emulated prepares disabled. Dynamic SQL is limited to fixed application-controlled fragments; user values remain bound parameters.
- **Ownership:** every task list/read/update/delete query includes the signed-in `user_id`. Knowing or changing a task ID does not grant access to another user's task.
- **CSRF:** mutating forms include a random session token. The server compares it with `hash_equals()` before processing POST data.
- **XSS:** HTML output uses `htmlspecialchars()` via `escape()`. The task detail dialog uses DOM text nodes/textContent for task text.
- **Sessions:** successful sign-in and password changes rotate the session ID. Cookies use HttpOnly and SameSite=Lax, and Secure when HTTPS is active.
- **Errors:** database details go to the server log; users receive a generic connection error rather than a raw SQL exception.
- **Roles:** there is one role, Registered User. Users can manage their own account and tasks; guests can register/sign in. There is no admin role.

## Likely review questions

**Why is `user_id` checked in the SQL if the task ID is unique?**  
An ID is not an authorization check. Adding the current user's ID to the condition means a forged or guessed ID cannot read or alter another user's record.

**Why use prepared statements?**  
The SQL structure is kept separate from user-supplied values, so inputs are treated as data instead of executable SQL.

**Why hash passwords instead of encrypting them?**  
The application only needs to verify a password, not recover it. A password hash is one-way and designed for password verification.

**Why validate on both client and server?**  
Browser checks improve usability, but requests can be sent without the browser UI. The server is the trusted place to enforce rules.

**What happens when a user deletes an account?**  
The foreign key is configured to cascade deletion of that user's tasks. This project does not currently provide an account-deletion screen.

**What are the current limitations?**  
The application has been run end to end against PHP 8.2 and MariaDB 10.11 in local Docker containers, and the 13-case manual test plan passed on 13 November 2026 (see `MANUAL_TESTS.md`). The app has no password reset, email verification, login throttling, admin role, or session revocation across other devices after a password change.

## Suggested demo sequence

1. Register two disposable test users.
2. Sign in as the first user, create a task, change its status, then search/filter it.
3. View and edit the task; close Edit and show that Create starts with a blank form.
4. Open the delete confirmation and cancel, then confirm on a disposable task.
5. Sign in as the second user and show that the first user's tasks do not appear.
6. Open Account settings and explain the current-password check before changing email or password.

Do this demonstration only after setting up a local PHP/MySQL environment and verifying each step there.
