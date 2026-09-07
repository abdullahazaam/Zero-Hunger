# Zero Hunger

Zero Hunger is a student project based on the problem of usable food being wasted while nearby organisations may need it. The application is a PHP and MySQL prototype that connects food donors, NGOs, riders and administrators.

## How it works

1. A donor lists available food.
2. An NGO reviews the listing and sends a request.
3. The donor accepts or rejects the request.
4. A rider can be assigned to collect and deliver the food.
5. The users can follow the request and delivery status.

## Main features

- Registration and sign-in for different user roles
- Food donation listings and requests
- Donor and NGO dashboards
- Rider assignment and delivery tracking
- Messages and notifications
- Admin screens for users and activity
- Feedback after a completed delivery

## Technology

- PHP
- MySQL
- HTML, CSS and JavaScript
- Bootstrap

## Running it locally

1. Place the project in your local web-server folder, such as `htdocs` for XAMPP.
2. Create a MySQL database.
3. Update the database settings in `Config/db.php`.
4. Import the project database schema if it is available in your copy.
5. Open the project through Apache.

The repository does not currently include a complete migration or schema setup, so a fresh installation still needs that work. Automated tests are also not included yet.

## Project status

This is a portfolio prototype rather than a production charity platform. A live deployment would need a repeatable database setup, stronger security review, file-upload protection, email configuration and full end-to-end testing.

