# Includes Directory

This directory contains configuration files and common includes used throughout the application.

## Files:

- **config.php** - Main configuration file containing:
  - Database credentials
  - Path constants
  - Database connection
  - Session management
  - Security helper functions
  - User authentication functions

## Security:

This directory is protected by `.htaccess` rules to prevent direct web access.
Files should only be included via `require_once` or `include_once` statements.
