# Helpdesk Ticketing System

A web-based helpdesk ticketing system that allows users to submit, track, and resolve support requests efficiently. Built with core PHP, HTML, CSS, and vanilla JavaScript using a MySQL database.

## Problem Statement

Organizations often struggle to manage and prioritize incoming support requests. Without a structured system, issues get lost in email threads, response times increase, and there is no visibility into workload distribution or resolution progress. This system provides a centralized platform where support requests are logged, assigned, tracked, and resolved in a timely manner.

## Features

- **User Registration and Authentication** -- Register, log in, and manage profiles with role-based access control (Staff, Faculty, Student).
- **User Management** -- Staff can create and delete user accounts from a dedicated management page.
- **Ticket Creation** -- Users can create support tickets with a subject, description, category, and priority.
- **Ticket Management Dashboard** -- View, filter, and sort all tickets. Assign tickets to staff members.
- **Status Tracking** -- Tickets move through defined statuses: Open, In Progress, Resolved, Closed.
- **Priority and Categories** -- Categorize tickets (Technical, Billing, General Inquiry, etc.) and set priority levels (Low, Medium, High, Critical).
- **Search and Filter** -- Search by keyword, status, priority, category, or date range.

## Tech Stack

- **Backend:** PHP 7.4+ (no frameworks)
- **Frontend:** HTML, CSS, vanilla JavaScript
- **Database:** MySQL
- **Server:** Apache (XAMPP)

## Project Structure (projected)

```
Project-cse311/
|-- index.php                   # Entry point / router
|-- config/
|   |-- constants.php           # Application constants
|   |-- database.php            # Database connection config
|-- includes/
|   |-- auth.php                # Authentication handlers
|   |-- functions.php           # Shared utility functions
|   |-- header.php              # Common page header
|   |-- footer.php              # Common page footer
|   |-- session.php             # Session management
|-- pages/
|   |-- login.php               # Login page
|   |-- register.php            # Registration page
|   |-- dashboard.php           # Main dashboard
|   |-- create_ticket.php       # Create new ticket
|   |-- manage_tickets.php      # Manage existing tickets
|   |-- manage_users.php        # User creation and deletion (staff only)
|   |-- view_ticket.php         # View individual ticket
|   |-- categories.php          # Manage ticket categories
|   |-- profile.php             # User profile page
|-- assets/
|   |-- css/                    # Stylesheets
|   |-- js/                     # JavaScript files
|-- sql/
|   |-- schema.sql              # Database schema
|   |-- sample.sql                # Sample data for testing
|-- docs/
|   |-- database_schema.md      # Database documentation
|   |-- database_erd.drawio     # Entity relationship diagram
|   |-- project_plan.md         # Project planning document
```

## Prerequisites

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 7.4 or higher)

## Setup Instructions

1. **Clone the repository** into the XAMPP `htdocs` directory:
   ```
   cd C:\xampp\htdocs
   git clone <repository-url> Project-cse311
   ```

2. **Create the database:**
   - Start Apache and MySQL from the XAMPP Control Panel.
   - Open phpMyAdmin at `http://localhost/phpmyadmin`.
   - Create a new database (e.g., `helpdesk_db`).
   - Import `sql/schema.sql` to set up the tables.
   - (Optional) Import `sql/sample.sql` to load sample data for testing.

3. **Configure the database connection:**
   - Open `config/database.php`.
   - Update the database host, name, username, and password to match your local setup.

4. **Run the application:**
   - Open a browser and go to `http://localhost/Project-cse311`.

## User Roles

| Role    | Permissions                                                        |
|---------|--------------------------------------------------------------------|
| Staff   | Full access: create/delete users, manage categories and all tickets |
| Faculty | Create tickets, track own ticket status                             |
| Student | Create tickets, track own ticket status                             |

## Sample Login Credentials

After importing `sql/sample.sql`, use any of the following accounts:

| Role    | Email                         | Password     |
|---------|-------------------------------|--------------|
| Staff   | admin@northsouth.edu          | staff123     |
| Faculty | kamal.hossain@northsouth.edu  | faculty123   |
| Student | sakib.hasan@northsouth.edu    | student123   |

## License

This project is developed for academic purposes (CSE311).
