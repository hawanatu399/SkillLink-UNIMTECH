# SkillLink UNIMTECH

SkillLink UNIMTECH is a web platform that lets students at the University of
Management and Technology (UNIMTECH) share academic and technical skills,
collaborate on projects, join study groups, exchange learning resources, and
build a professional academic profile. Lecturers can moderate content and
verify student skills; administrators have full oversight of the platform.

## What Makes This Different

Most student platforms (Discord servers, WhatsApp groups, generic classroom
tools) are passive: they give students a place to post, but nothing helps
them find the *right* person to work with. SkillLink UNIMTECH does two
things a passive directory doesn't:

1. **A skill-matching recommendation engine** (`includes/matching.php`).
   Instead of only letting a student search manually, the dashboard actively
   recommends collaborators, scored and explained: shared skills at a
   complementary level (mentorship opportunities) are weighted higher than
   shared skills at the same level, lecturer-verified skills are weighted
   higher than self-reported ones, and department overlap contributes a
   smaller, practical bonus. Every recommendation shows *why* it was made
   — the score is fully explainable, not a black box.

2. **A reputation system built from things someone else vouched for**
   (`includes/reputation.php`), not raw activity counts. A student's score
   is calculated from lecturer-verified skills, lecturer-approved resources,
   completed collaborations, and peer review ratings — recalculated
   automatically whenever a lecturer verifies a skill, approves a resource,
   a collaboration is accepted, or a review is submitted. This score feeds
   directly into the matching engine, so consistently helpful students are
   surfaced more often.

3. **An active marketplace, not just a passive profile** (`student/marketplace.php`).
   Skills listed on a profile are useful for the matching engine, but they don't
   let a student explicitly say "I'm available to teach this right now" or
   "I'm actively looking for help with this." The marketplace is a browsable
   board where students post one of two listing types — Offering or Seeking —
   with a description and optional availability window, searchable by skill
   and filterable by type. Browsing students can contact a poster directly
   through the existing collaboration-request flow. This gives students who
   want to act immediately a direct path, alongside the passive, algorithmic
   recommendations from the matching engine.

Together, these turn the platform from "a place to list your skills" into
"a system that actively connects the right people," which is the core
argument for why this is a platform and not just a form-and-table CRUD app.

## Features

**Students**
- Register and manage a profile (bio, interests, profile picture)
- Add and showcase skills
- Find and search other students by department, programme, and level
- Send and respond to collaboration requests
- Create and join study groups
- Upload and browse learning resources (PDF, DOC, DOCX, PPT, PPTX, ZIP)
- Leave reviews after a collaboration
- Receive notifications for platform activity

**Lecturers**
- View and manage the student roster
- Verify student-submitted skills
- Approve or reject study groups and uploaded resources
- Review collaborations happening on the platform

**Admins**
- Platform-wide dashboard with usage statistics
- Manage all user accounts (search, change role, delete)
- Oversight of all resources and study groups platform-wide, with the
  ability to remove inappropriate content

## Tech Stack

- **Backend:** PHP (procedural, `mysqli` with prepared statements)
- **Database:** MySQL / MariaDB
- **Frontend:** Bootstrap 5 (via CDN), vanilla HTML/CSS
- **Auth:** PHP sessions, `password_hash()` / `password_verify()`

## Requirements

- PHP 8.0+ with the `mysqli` extension
- MySQL 5.7+ or MariaDB 10.3+
- A local server stack such as XAMPP, WAMP, MAMP, or `php -S`

## Setup

1. **Clone or copy the project** into your web server's document root, e.g.
   `htdocs/SkillLink-UNIMTECH` if using XAMPP.

2. **Create the database.** Import the provided schema:
   ```bash
   mysql -u root -p < schema.sql
   ```
   This creates the `skilllink_unimtech` database, all tables, and two
   seed accounts (see below).

3. **Configure the database connection**, if your setup differs from the
   defaults, in `config/database.php`:
   ```php
   $host = "localhost";
   $user = "root";
   $password = "";
   $database = "skilllink_unimtech";
   ```

4. **Start the server.** With XAMPP, start Apache and MySQL from the control
   panel. With PHP's built-in server, from the project root:
   ```bash
   php -S localhost:8000
   ```

5. **Visit the app** at `http://localhost/SkillLink-UNIMTECH/` (or
   `http://localhost:8000/` if using the built-in server).

## Test Accounts

The registration form only ever creates **student** accounts (by design —
lecturer and admin accounts represent staff, not self-service signups).
`schema.sql` seeds two staff accounts for testing:

| Role     | Email                          | Password      |
|----------|---------------------------------|----------------|
| Admin    | admin@unimtech.edu.sl           | Password123    |
| Lecturer | lecturer@unimtech.edu.sl        | Password123    |

Register a new account through `register.php` to test the student role.

**Change or remove these credentials before any real deployment.**

## Project Structure

```
SkillLink-UNIMTECH/
├── admin/              Admin-only pages (dashboard, user & content oversight)
├── lecturer/           Lecturer-only pages (student roster, moderation)
├── student/            Student-only pages (profile, skills, groups, resources)
├── config/             Database connection and session bootstrap
├── includes/           Shared auth, role-check, and CSRF helpers
├── templates/          Shared header, footer, and sidebar partials
├── assets/             CSS, JS, images (site currently styled via Bootstrap CDN)
├── uploads/             User-uploaded resources and profile pictures
├── schema.sql          Full database schema + seed accounts
├── index.php           Public landing page
├── login.php / login_process.php
├── register.php / register_process.php
└── logout.php
```

## Security Notes

- All database queries use prepared statements (`mysqli_prepare` /
  `bind_param`) to prevent SQL injection.
- Passwords are hashed with `password_hash()` (bcrypt) and never stored or
  logged in plain text.
- Every page under `student/`, `lecturer/`, and `admin/` enforces both a
  login check and a role check (`require_role()` in `includes/auth.php`),
  so a logged-in user cannot access another role's pages by guessing a URL.
- Every state-changing form is protected against CSRF using a per-session
  token (`includes/csrf.php`), verified before any database write.
- File uploads (resources and profile pictures) use randomly generated
  filenames, an extension whitelist, a file size cap, and — for images — a
  content check via `getimagesize()` to reject disguised files.

## Known Limitations

- The database schema was reconstructed from the application's queries
  rather than exported from a live database, since no `.sql` file existed
  in the original project. Verify column types against your data before
  a production deployment.
- There is no automated test suite.
- Email notifications are in-app only; no email/SMS delivery is implemented.

## Author

Developed as a final year project for the Department of Computer Science,
UNIMTECH.
