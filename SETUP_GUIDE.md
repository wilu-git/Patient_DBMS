# 🚀 Patient DBMS Setup Guide

## Quick Setup to Fix Login Issues

### Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** service
3. Start **MySQL** service

### Step 2: Create Database (Choose ONE method)

#### Method A: Using phpMyAdmin (Recommended)
1. Open browser and go to: `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Choose file: `database_schema.sql`
4. Click "Go" to execute

#### Method B: Using MySQL Command Line
```bash
mysql -u root -p < database_schema.sql
```

#### Method C: Using PHP Setup Script
1. Go to: `http://localhost/Patient_DBMS/database_setup.php`
2. This will create the database and tables automatically

### Step 3: Verify Database Creation
1. Go to phpMyAdmin: `http://localhost/phpmyadmin`
2. Check if `patient_dbms` database exists
3. Check if `users` table has 4 default users

### Step 4: Test Login
1. Go to: `http://localhost/Patient_DBMS/login.php`
2. Use these credentials:

| Role | Username | Password |
|------|----------|----------|
| Developer | admin | admin123 |
| Doctor | doctor1 | admin123 |
| Secretary | secretary1 | admin123 |
| Accountant | accountant1 | admin123 |

## 🔧 Troubleshooting

### If you still get "Invalid username or password":

#### Check 1: Database Connection
- Verify MySQL is running in XAMPP
- Check if `patient_dbms` database exists
- Check if `users` table has data

#### Check 2: Database Configuration
Open `config.php` and verify:
```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');  // Empty for XAMPP default
define('DB_NAME', 'patient_dbms');
```

#### Check 3: Manual User Creation
If users don't exist, run this SQL in phpMyAdmin:
```sql
USE patient_dbms;

INSERT INTO users (username, password, email, role, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@clinic.com', 'developer', 'System Administrator'),
('doctor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor@clinic.com', 'doctor', 'Dr. John Smith'),
('secretary1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretary@clinic.com', 'secretary', 'Jane Doe'),
('accountant1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'accountant@clinic.com', 'accountant', 'Bob Johnson');
```

## 🎯 Quick Test
1. Go to: `http://localhost/Patient_DBMS/login.php`
2. Username: `admin`
3. Password: `admin123`
4. You should see the dashboard

## ⚠️ Important Notes
- **Change default passwords** after first login
- Make sure XAMPP services are running
- Check browser console for any JavaScript errors
- Clear browser cache if needed

## 📞 Still Having Issues?
1. Check XAMPP error logs
2. Verify file permissions
3. Ensure all PHP files are in the correct directory
4. Check if PHP extensions are enabled (mysqli, session)
