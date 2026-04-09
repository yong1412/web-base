# FurniHome

An academic Core PHP project demonstrating user management and administration features.

## Features

- User Registration
- Email Verification (token-based)
- Profile Photo Upload with Drag & Drop
- Admin Member Listing with Search and Pagination
- Admin Member Detail View
- Block/Unblock Members
- Clean Academic UI with jQuery and SweetAlert

## Tech Stack

- PHP 8.2+
- MySQL with PDO
- HTML5 + CSS3 (custom)
- jQuery
- SweetAlert

## Setup

1. Create MySQL database `bmit_db`
2. Copy `app/lib/config-example.php` to `app/lib/config.php` and update the following:
    - Database credentials (host, username, password, database name)
    - Email credentials (SMTP host, port, username, password, encryption)
3. Run `php app/lib/db_setup.php` to create tables and sample data
4. Start server: `php -S localhost:8000`
5. Visit `http://localhost:8000`

## Project Structure
```
app/
├── css/
│   └── app.css          # Main stylesheet for the application
├── js/
│   ├── app.js           # Main JavaScript for UI interactions
│   └── validation.js    # Form validation scripts
├── lib/                 # Core library / helper scripts
│   ├── config-example.php  # Sample configuration template
│   ├── config.php          # Main configuration file
│   ├── base.php            # Base functions / bootstrap
│   ├── auth.php            # Authentication logic
│   ├── validator.php       # Server-side validation helpers
│   ├── html_helper.php     # HTML rendering helpers
│   ├── mailer.php          # Email sending utility
│   └── db_setup.php        # Database initialization / connection
├── page/                 # Page templates and sections
│   ├── _base.php          # Base page wrapper
│   ├── _head.php          # HTML <head> section
│   ├── _foot.php          # HTML footer section
│   ├── auth/              # Authentication pages (login, register, etc.)
│   ├── admin/             # Admin dashboard pages
│   └── member/            # Member-specific pages
└── index.php              # Entry point of the application
```


## Notes

- Authentication and authorization logic is stubbed for implementation by another team member
- All database queries use PDO prepared statements
- Server-side validation with error messages
- Reusable helpers and academic coding style