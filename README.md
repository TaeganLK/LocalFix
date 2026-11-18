# LocalFix
### Web-Based Municipal Fault Reporting and Service Request Management System

## 1. Project Overview

LocalFix is a web-based municipal fault reporting and service request management system developed to provide residents with a simple way to report local infrastructure and service-related problems.

The system allows residents to register, log in, submit fault reports and track the progress of their submissions. Administrators can access a central dashboard to view submitted reports, update their statuses and record administrative notes.

LocalFix was developed as an academic project to demonstrate the application of web development, database management, authentication and information security principles at Richfield Graduate Institute of Technology.

**Important:** This is an academic prototype and is not connected to an official municipal reporting system.

## 2. Main Features

### Resident Features
- Resident account registration.
- Secure login and logout.
- Resident dashboard.
- Submit municipal fault reports.
- Generate unique report reference numbers.
- View previously submitted reports.
- Track report statuses.
- View report updates.

### Administrator Features
- Administrator login.
- Centralised administrative dashboard.
- View submitted resident reports.
- View individual report details.
- Update report statuses.
- Add administrative notes.
- Maintain a history of report status updates.

## 3. Technologies Used

- PHP
- MySQL / MariaDB
- HTML5
- CSS3
- JavaScript
- Bootstrap
- XAMPP
- Git and GitHub

## 4. System Requirements

To run LocalFix locally, the following software is required:

- Windows, Linux or macOS.
- XAMPP or another compatible PHP and MySQL development environment.
- PHP.
- MySQL or MariaDB.
- A modern web browser.
- Git (optional, for cloning the repository).

## 5. Installation and Setup

### Step 1: Obtain the Project

Clone the GitHub repository:

```bash
git clone https://github.com/TaeganLK/LocalFix.git
```

Alternatively, download the repository as a ZIP file and extract it into your web server's document root.

For XAMPP on Windows, the project folder should be:

```text
C:\xampp\htdocs\LocalFix
```

### Step 2: Start XAMPP

Open the XAMPP Control Panel and start:

- Apache
- MySQL

### Step 3: Create the Database

1. Open phpMyAdmin in your browser:

   http://localhost/phpmyadmin

2. Create a database named:

   `localfix_db`

3. Select the newly created database.
4. Open the Import tab.
5. Import the `database.sql` file from the project folder.

This creates the database tables and inserts the initial report categories.

### Step 4: Configure the Database Connection

The repository includes an example configuration file:

`config/database.example.php`

Copy this file and create:

`config/database.php`

Update the database connection settings to match your local MySQL configuration.

The local database configuration file is excluded from Git to prevent sensitive connection information from being uploaded.

### Step 5: Run the Application

Open the following URL:

http://localhost/LocalFix/

The application should display the LocalFix public homepage.

Register a resident account to access the resident functionality.

An administrator account must be created or configured separately according to the database setup.

## 6. Report Categories

The system supports the following report categories:

- Potholes
- Streetlights
- Water leaks
- Blocked drains
- Illegal dumping
- Traffic lights
- Other

## 7. Database Structure

The system uses a relational database containing the following main tables:

| Table | Purpose |
|---|---|
| `users` | Stores resident and administrator account information. |
| `categories` | Stores available fault report categories. |
| `reports` | Stores submitted fault reports and their current statuses. |
| `report_updates` | Records report status changes and administrative notes. |

## 8. Security Features

The application incorporates several security measures:

- Password hashing for account credentials.
- Prepared SQL statements to reduce SQL injection risks.
- Session-based authentication.
- Role-based access control for administrator functionality.
- CSRF token validation for protected forms and actions.
- Session ID regeneration during authentication.
- Secure session cookie settings.
- Resident-specific report access restrictions.

## 9. User Roles

### Resident

Residents can create accounts, submit fault reports and monitor their own submissions.

### Administrator

Administrators can view reports submitted through the system, manage report statuses and record administrative updates.

## 10. Project Limitations

LocalFix is an academic prototype. It does not currently communicate with municipal databases, automatically dispatch maintenance teams or provide real-time municipal service updates.

All report processing and status changes are demonstrated within the application itself.

## 11. Repository

GitHub repository:

https://github.com/TaeganLK/LocalFix

## 12. Project Purpose

The purpose of LocalFix is to demonstrate how a web-based information system can improve the organisation and management of municipal fault reports.

The project applies software development principles, relational database design, user authentication, access control and user interface design to create a functional prototype.