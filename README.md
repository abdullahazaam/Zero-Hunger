# Zero Hunger

Zero Hunger is a PHP/MySQL portfolio project exploring how surplus food can move from donors to NGOs and community recipients through a trackable rider-delivery workflow.

## Problem and approach

Usable food is often wasted while nearby communities need affordable access to meals. The application models a role-based redistribution flow instead of a simple listing page: donors publish available food, NGOs claim suitable donations, riders complete delivery, and administrators monitor activity.

## Features in the repository

- Donor, NGO/receiver, rider, and administrator roles
- Registration, login, email verification, password reset, and profile management
- Food donation listing and location-aware discovery
- Donation claims and delivery setup
- Rider enrolment, assignment, delivery status, and ratings
- Direct messages and email/push notification hooks
- Admin dashboards for users, donations, requests, riders, deliveries, feedback, activity logs, and reports
- English and Urdu language resources
- Invoice and report/export flows

## Technology

- PHP with MySQLi
- MySQL/MariaDB
- HTML, CSS, Bootstrap, and JavaScript
- Session-based authentication
- Progressive-web-app support files

## Structure

```text
Zero-Hunger/
├── Frontend/   Public pages and role dashboards
└── Backend/    Database connection, admin tools, reports, and shared logic
```

## Local setup

1. Install PHP 8+ and MySQL/MariaDB (XAMPP is suitable for local development).
2. Place the repository inside the web server document root.
3. Create a `zero_hunger` database.
4. Provide the schema used by the application and configure local values in `Backend/db.php`.
5. Update local site URLs and optional mail/push settings without committing real credentials.
6. Open `Frontend/` through the local web server.

> The repository currently does not include a clean, data-free database migration/schema file. That is a documented setup limitation and the highest-priority portability improvement.

## Verification

The included GitHub workflow performs syntax checks on tracked PHP files. A database-backed automated test suite is not currently present; functional claims should therefore be demonstrated with a local walkthrough rather than described as fully tested.

## Security notes

- Do not commit real SMTP, OAuth, database, VAPID, or analytics credentials.
- Uploaded identity/profile files must be validated and stored outside executable paths for a real deployment.
- Database queries should continue moving toward prepared statements and centralised validation.
- Production use requires CSRF protection, authorization tests, HTTPS, audit review, backups, and privacy controls.

## Portfolio status

Zero Hunger is best presented as a social-impact full-stack prototype. Its strongest portfolio value is the multi-role business workflow; the next engineering milestone is a reproducible schema plus automated tests.

## Author

**Abdullah Azaam** — web developer working with PHP, Laravel, ASP.NET Core, C#, and SQL databases.

