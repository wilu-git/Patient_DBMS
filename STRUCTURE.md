# Patient DBMS - Folder Structure Documentation

## Overview

This document describes the reorganized folder structure following PHP development best practices.

## Directory Structure

```
Patient_DBMS/
├── .htaccess              # Apache configuration and security rules
├── index.php              # Root redirector to public/index.php
├── README.md              # Main documentation
├── SETUP_GUIDE.md         # Setup instructions
├── STRUCTURE.md           # This file
│
├── includes/              # Configuration and shared code
│   ├── config.php        # Database config, session, helpers
│   └── README.md         # Documentation for includes
│
├── public/               # Web-accessible directory (document root)
│   ├── index.php        # Main dashboard
│   ├── login.php        # User authentication
│   ├── logout.php       # Session termination
│   ├── error.php        # Generic error page
│   ├── unauthorized.php # Access denied page
│   ├── backup.php       # Database backup utility
│   ├── create_users.php # User management
│   │
│   ├── assets/          # Static resources
│   │   ├── css/        # Custom stylesheets
│   │   └── js/         # Custom JavaScript
│   │
│   └── views/           # Feature-specific views
│       ├── patients/        # Patient management
│       │   ├── patients.php        # Patient list
│       │   ├── patient_create.php  # Add new patient
│       │   ├── patient_read.php    # View patient details
│       │   ├── patient_update.php  # Edit patient
│       │   └── patient_delete.php  # Delete patient
│       │
│       ├── appointments/    # Appointment scheduling
│       │   ├── appointments.php       # Appointment list
│       │   └── appointment_create.php # Schedule appointment
│       │
│       ├── billing/         # Billing and invoicing
│       │   ├── billing.php        # Billing list
│       │   └── billing_create.php # Create bill
│       │
│       └── transactions/    # Payment processing
│           └── transactions.php   # Transaction list
│
└── setup/                # Installation and diagnostics
    ├── database_setup.php    # Database installer
    ├── database_schema.sql   # Database schema
    ├── setup.php            # Setup wizard
    └── diagnose.php         # System diagnostics

```

## Design Principles

### 1. Separation of Concerns
- **includes/**: Backend configuration and shared utilities
- **public/**: Frontend, user-facing pages
- **setup/**: Installation and maintenance tools

### 2. Security
- Configuration files in `includes/` are protected via `.htaccess`
- Only `public/` directory should be exposed to web
- Sensitive files cannot be accessed directly via HTTP

### 3. Maintainability
- Related features grouped in subdirectories
- Clear naming conventions
- Consistent file organization

### 4. Scalability
- Easy to add new modules in `public/views/`
- Assets organized for future growth
- Modular structure supports team development

## File Path Conventions

### Including config.php

From `public/` directory:
```php
require_once "../includes/config.php";
```

From `public/views/*/` directories:
```php
require_once "../../../includes/config.php";
```

From `setup/` directory:
```php
require_once "../includes/config.php";
```

### Navigation Links

From `public/` files to views:
```php
header("location: views/patients/patients.php");
```

From views to `public/` root:
```php
header("location: ../../index.php");
```

Between view directories:
```php
header("location: ../billing/billing.php");
```

## Benefits of This Structure

### For Developers
- Clear organization makes code easy to find
- Separation of concerns improves code quality
- Easier to implement new features
- Better for version control and collaboration

### For Security
- Protected configuration files
- Clear separation of public vs private code
- Easier to implement access controls
- Better support for security best practices

### For Deployment
- Can set document root to `public/` directory
- Clear separation of setup vs production code
- Easier to configure web server rules
- Better support for different environments

## Migration Notes

All file paths have been updated to work with the new structure:
- Database connection via `includes/config.php`
- Navigation links updated for new paths
- Form actions updated for current directory
- Session management centralized in config

## Future Enhancements

Possible additions to this structure:
- `models/` - Database interaction classes
- `controllers/` - Business logic
- `templates/` - Reusable view components
- `tests/` - Unit and integration tests
- `logs/` - Application logs
- `uploads/` - User uploaded files
- `cache/` - Cached data

## Standards Compliance

This structure follows:
- PHP-FIG recommendations
- MVC design pattern concepts
- OWASP security guidelines
- General web development best practices
