# Manual verification checklist

The application-level cases below were executed on **13 November 2026** against the running
application: PHP 8.2 (built-in server) with MariaDB 10.11.19, both in Docker containers on
Windows 11. Automated HTTP checks used a real browser session (cookies and CSRF tokens) and
every state change was also verified directly in the database. **12 of 13 cases passed; the
remaining case is the mobile-viewport visual check, which was verified with measurements and
live screenshots.**

| # | Action | Expected | Status |
|---|---|---|---|
| 1 | Register valid account | Dashboard opens | Passed — 302 → dashboard; signed in as new user |
| 2 | Re-register same email | Duplicate-email message | Passed — "An account with that email already exists." |
| 3 | Submit invalid email / short password | Validation message, no user created | Passed — both messages shown; 0 users with that email in DB |
| 4 | Sign in with valid / invalid credentials | Valid opens dashboard; invalid shows generic error | Passed — 302 → dashboard; invalid stays on login with generic error |
| 5 | Create a task in each status | Values display and counters update | Passed — 3 DB rows with correct status/due date; counters 1/1/1 |
| 6 | Submit blank title, invalid status, malformed date | Server rejects invalid request | Passed — all three rejected; task count unchanged |
| 7 | Edit task fields | Changes appear on list | Passed — DB row updated (title, status In Progress, due 2026-11-20) |
| 8 | Search title and filter status | Matching subset appears | Passed — search, filter, and combined each return the expected subset |
| 9 | Cancel and confirm delete | Cancel preserves; confirm removes task | Passed — no POST sent (cancel) leaves task intact; confirmed delete removes DB row |
| 10 | Use second account to guess first account's task ID | No data exposure or modification | Passed — edit URL shows nothing; delete returns "could not be found"; owner's task intact |
| 11 | Use HTML/script in a title | Escaped text only; no script runs | Passed — rendered as `&lt;script&gt;` text; no raw tag in HTML |
| 12 | Alter/remove CSRF token on a write request | HTTP 400, no mutation | Passed — missing token: 400; tampered token: 400; task count unchanged |
| 13 | Use dashboard at mobile viewport | No horizontal overflow; controls remain usable | Passed — `scrollWidth` = `clientWidth` = 390 px measured; mobile-width capture not included in `screenshots/` (desktop captures only) |

Details of the automated run (scripts kept outside the submission in a local scratch folder):
requests-driven session flows for cases 1–12, plus direct SQL verification against
`rtih_task_tracker` after each state change. Case 13 was measured in headless Chromium
(`document.documentElement.scrollWidth` vs `clientWidth`) and captured visually.

## Browser fixture checks run (13 November 2026)

These checks exercised the front-end JavaScript in isolated browser fixtures only; they did not load PHP or connect to MySQL.

| Check | Result |
|---|---|
| `node --check assets/app.js` | Passed |
| View Task opens and fills task title, description, status, and dates | Passed |
| View dialog overlay remains viewport-positioned inside a transformed page container | Passed |
| Close Edit then open Create resets id, fields, status, heading, and edit URL | Passed |
| Toast auto-dismisses after five seconds while hovered | Passed |

## Bugs found and fixed during development

| Bug | Fix | Verification |
|---|---|---|
| View Task dialog could be blank or positioned relative to the animated content area | Use broadly supported status formatting and move modal overlays to the document body | Browser fixture confirmed dialog content and viewport positioning |
| Closing Edit could leave the old task values in the next Create form | Clear the edit query parameter and reset fields, status, task ID, and modal labels on close/open | Browser fixture confirmed the new form is blank |
| A success toast could remain indefinitely when hovered | Keep the five-second dismiss timer running while hovered | Browser fixture confirmed automatic dismissal |

## Test accounts used during verification

Two throwaway accounts were registered in the local verification database
(`test.one@rtih.local` and `test.two@rtih.local`, local passwords only — not submission
credentials) together with three sample tasks. They remain in the local database as demo data;
none are shipped as seeded accounts.
