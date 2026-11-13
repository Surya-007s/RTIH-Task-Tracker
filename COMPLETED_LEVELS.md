# Completed levels and limitations

## Completed

- **Level 1:** PDO connection setup; task create, list, edit, delete; delete confirmation; success and error messages. Database connectivity is implemented but not verified in this workspace.
- **Level 2:** Pending, In Progress, and Completed states; title search; status filter; front-end and back-end validation; due date support.
- **Level 3:** Registration/login; password hashing and verification; session management; user-specific access enforced in server-side queries; prepared statements; output escaping; CSRF tokens.
- **Account controls:** users can change their email and password after confirming their current password.
- **Roles and permissions:** the single role is Registered User. Users manage only their own account and tasks; guests cannot access the dashboard. There is no administrator role.
- **Interface polish:** full-width task area, date badge, status-distribution bars, responsive cards and controls, keyboard focus styling, and reduced-motion-aware transitions.

## Limitations and verification status

- The current workspace had no PHP executable or MySQL/MariaDB server/client. End-to-end execution and database-backed manual tests remain to be run on a machine with the requirements in README.md.
- The images in `screenshots/` are design previews, not captures from a live app session.
- JavaScript/browser fixture checks were run for dialog data, edit-to-create form reset, overlay position, toast auto-dismiss, and script syntax. PHP, SQL, authentication, authorization, and database-backed task flows were not run.
- No shared account or password is seeded. Register a disposable local test account after database setup.
- Password reset, email verification, login throttling, and admin functions are outside this assignment implementation.

## AI disclosure

OpenAI Codex and ChatGPT were used; generated code was reviewed. One reviewed change added `user_id` conditions to task list, edit, update, and delete queries, so changing an ID in the browser cannot bypass task ownership checks.
