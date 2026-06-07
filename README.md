# WordPress Developer Screening System

A lightweight applicant screening web application for filtering and shortlisting WordPress developer candidates.

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB
- Apache with mod_rewrite (XAMPP, WAMP, Laragon, etc.)

## Installation

### Option 1: Web Installer (Recommended)

1. Copy the project folder to your web server's document root (e.g., `htdocs/hiring-management`)
2. Open `http://localhost/hiring-management/install.php` in your browser
3. The installer will create the database, tables, and seed the admin account
4. **Delete `install.php` after installation**

### Option 2: Manual Setup

1. Import the database schema:
   ```
   mysql -u root -p < database.sql
   ```
2. Edit `config/database.php` with your MySQL credentials if different from defaults
3. Ensure `uploads/cv/` directory is writable

## Configuration

Edit `config/database.php` to update:
- Database host, name, username, password
- Site URL and Admin URL

## Usage

### Public Application URL
```
http://localhost/hiring-management/public/index.php
```
Share this URL in your LinkedIn post for applicants to apply.

### Admin Dashboard
```
http://localhost/hiring-management/admin/login.php
```

**Default Credentials:**
- Username: `admin`
- Password: `admin123`

## Project Structure

```
hiring-management/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── auth.php               # Authentication helpers
│   ├── functions.php          # Core utility functions
│   └── scoring.php            # Keyword-based scoring engine
├── public/
│   ├── index.php              # Application form
│   ├── test.php               # Screening test
│   ├── thankyou.php           # Submission confirmation
│   └── assets/
│       ├── css/style.css      # Custom styles
│       └── js/app.js          # Client-side validation
├── admin/
│   ├── login.php              # Admin login
│   ├── logout.php             # Admin logout
│   ├── index.php              # Dashboard with stats & applicant table
│   ├── applicant.php          # Applicant detail view
│   ├── save-notes.php         # AJAX endpoint for admin notes
│   └── export.php             # CSV export of shortlisted candidates
├── uploads/
│   └── cv/                    # Uploaded CVs (PDF)
├── database.sql               # Database schema
├── install.php                # Web installer
└── README.md
```

## Features

- Auto-rejection based on city, experience, and portfolio
- 5-question WordPress screening test
- Keyword-based scoring engine (0-100)
- Status assignment: Shortlisted (70+), Manual Review (50-69), Rejected (<50)
- Admin dashboard with stats cards, search, and status filtering
- Applicant detail view with score breakdown and matched keywords
- Admin notes per applicant
- Manual status override
- CSV export of shortlisted candidates
- Email notification on submission
- Mobile responsive (Bootstrap 5)
