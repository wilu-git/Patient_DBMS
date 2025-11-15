# Security Best Practices for Patient DBMS

## Overview

This document outlines the security measures implemented in Patient DBMS and best practices for maintaining a secure deployment.

## Security Features Implemented

### 1. Authentication & Authorization

#### Password Security
- **Password Hashing**: All passwords are hashed using PHP's `password_hash()` with `PASSWORD_DEFAULT` algorithm (currently bcrypt)
- **Minimum Password Length**: 6 characters (should be increased to 8+ in production)
- **Secure Password Storage**: Never stores passwords in plain text

#### Session Security
- **Session ID Regeneration**: Sessions are regenerated periodically and after login to prevent session fixation attacks
- **Secure Cookie Flags**: 
  - `httponly`: Prevents JavaScript access to session cookies
  - `secure`: Ensures cookies are only sent over HTTPS (when available)
  - `samesite=Strict`: Prevents CSRF attacks via cross-site requests

#### Rate Limiting
- **Login Attempt Limiting**: Maximum 5 failed login attempts per username within 15 minutes
- **Failed Login Tracking**: All failed login attempts are logged in the audit_log table

### 2. SQL Injection Prevention

#### Prepared Statements
- **Parameterized Queries**: All database queries use prepared statements with parameter binding
- **No String Concatenation**: User input is never directly concatenated into SQL queries

Example:
```php
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $username);
```

### 3. Cross-Site Scripting (XSS) Prevention

#### Output Encoding
- **HTML Entity Encoding**: All user-generated content is escaped using `htmlspecialchars()` before display
- **Enhanced Sanitization**: Custom `sanitize_output()` function with proper encoding flags

Example:
```php
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');
```

### 4. Cross-Site Request Forgery (CSRF) Protection

#### CSRF Tokens
- **Token Generation**: Cryptographically secure tokens generated using `random_bytes()`
- **Token Validation**: All state-changing requests (POST, PUT, DELETE) require valid CSRF tokens
- **Token Storage**: Tokens are stored in session and validated using `hash_equals()` for timing-attack resistance

Implementation:
```php
// In forms:
<?php echo csrf_token_field(); ?>

// In processing:
if (!verify_csrf_token($_POST['csrf_token'])) {
    die("Invalid CSRF token");
}
```

### 5. Database Security

#### Connection Security
- **UTF-8 Character Set**: All database connections use UTF-8 to prevent character encoding attacks
- **Error Handling**: Database errors are logged but not displayed to users in production
- **Prepared Statements**: See SQL Injection Prevention above

#### Data Integrity
- **Foreign Key Constraints**: Maintain referential integrity between tables
- **Soft Deletes**: Uses `is_active` flags instead of hard deletes to preserve audit trails
- **Database Indexes**: Optimized queries with proper indexing

### 6. Audit Logging

#### Activity Tracking
All critical actions are logged in the `audit_log` table:
- User login/logout
- Failed login attempts
- Data creation, updates, and deletions
- Password changes

Logged information includes:
- User ID
- Action performed
- Table and record affected
- Old and new values (for updates)
- IP address
- User agent
- Timestamp

### 7. Access Control

#### Role-Based Access Control (RBAC)
Four user roles with different permissions:
- **Developer**: Full system access, user management, backup
- **Doctor**: Patient records, appointments, billing (view)
- **Secretary**: Patient management, appointments, billing, transactions
- **Accountant**: Financial reports, billing, transactions (view)

#### Authorization Checks
```php
// Require login for all protected pages
require_login();

// Require specific role(s)
require_role([ROLE_DEVELOPER, ROLE_ACCOUNTANT]);
```

### 8. File Security

#### .htaccess Protection
- **Directory Browsing Disabled**: `Options -Indexes`
- **Sensitive File Protection**: Config files and includes directory are protected
- **Security Headers**: X-Content-Type-Options, X-Frame-Options, X-XSS-Protection

## Deployment Best Practices

### 1. Environment Configuration

#### Use Environment Variables
- **Don't hardcode credentials**: Use `.env` file (not committed to git)
- **Copy `.env.example`** to `.env` and update with production values
- **Restrict file permissions**: `chmod 600 .env`

#### Production Settings
```php
// In production, ensure:
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
```

### 2. HTTPS/SSL

**Always use HTTPS in production:**
- Obtain SSL certificate (Let's Encrypt is free)
- Force HTTPS in Apache/Nginx configuration
- Update session cookie settings to require secure flag

### 3. Database Security

#### Secure Database Credentials
- Use strong, unique passwords
- Create dedicated database user with minimal privileges
- Don't use root database account

#### Database User Permissions
```sql
CREATE USER 'patient_dbms'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON patient_dbms.* TO 'patient_dbms'@'localhost';
FLUSH PRIVILEGES;
```

### 4. File Permissions

#### Recommended Permissions
```bash
# Directories: 755
find /path/to/Patient_DBMS -type d -exec chmod 755 {} \;

# PHP files: 644
find /path/to/Patient_DBMS -type f -name "*.php" -exec chmod 644 {} \;

# Config and .env: 600
chmod 600 /path/to/Patient_DBMS/includes/config.php
chmod 600 /path/to/Patient_DBMS/.env
```

### 5. Regular Updates

- Keep PHP updated to latest stable version
- Keep MySQL/MariaDB updated
- Monitor security advisories for dependencies
- Review audit logs regularly

### 6. Backup Strategy

- **Regular Backups**: Schedule daily database backups
- **Secure Storage**: Store backups in secure, encrypted location
- **Test Restores**: Regularly test backup restoration process
- **Retention Policy**: Keep backups for appropriate period (30-90 days)

### 7. Additional Recommendations

#### Password Policy
- Enforce minimum 8-character passwords (update validation)
- Require password complexity (uppercase, lowercase, numbers, symbols)
- Implement password expiration (e.g., every 90 days)
- Prevent password reuse

#### Two-Factor Authentication
Consider implementing 2FA for sensitive roles (Developer, Accountant)

#### IP Whitelisting
For developer/admin accounts, consider IP-based access restrictions

#### Security Monitoring
- Monitor failed login attempts
- Set up alerts for suspicious activity
- Review audit logs weekly
- Implement intrusion detection

## Security Checklist for Deployment

- [ ] Change all default passwords
- [ ] Update database credentials in `.env`
- [ ] Enable HTTPS/SSL
- [ ] Set proper file permissions
- [ ] Disable PHP error display in production
- [ ] Configure secure session settings
- [ ] Set up database backups
- [ ] Review and restrict database user permissions
- [ ] Test CSRF protection on all forms
- [ ] Verify audit logging is working
- [ ] Set up security monitoring/alerts
- [ ] Document any custom security measures
- [ ] Train users on security best practices
- [ ] Establish incident response plan

## Reporting Security Issues

If you discover a security vulnerability, please report it to the system administrator immediately. Do not disclose security issues publicly until they have been addressed.

## References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [MySQL Security Best Practices](https://dev.mysql.com/doc/refman/8.0/en/security.html)

---

Last Updated: 2024
