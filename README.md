# Patient Database Management System (Patient DBMS)

A comprehensive clinic management system designed to track cashflow, manage patient records, schedule appointments, and handle billing with role-based access control.

## 🏥 System Overview

This system addresses the critical need for transparent financial tracking in clinics, preventing overcharging and ensuring proper cashflow management. It provides different access levels for doctors, secretaries, developers, and accountants.

## 📁 Project Structure

The project follows PHP development best practices with organized folders:

```
Patient_DBMS/
├── includes/           # Configuration and common includes
│   └── config.php     # Database config, session management, helper functions
├── public/            # Publicly accessible files
│   ├── index.php     # Main dashboard
│   ├── login.php     # Login page
│   ├── logout.php    # Logout handler
│   ├── backup.php    # Database backup utility
│   ├── views/        # Feature-specific views
│   │   ├── patients/      # Patient management (CRUD operations)
│   │   ├── appointments/  # Appointment scheduling
│   │   ├── billing/       # Billing and invoicing
│   │   └── transactions/  # Payment transactions
│   └── assets/       # Static assets (CSS, JS, images)
├── setup/            # Installation and diagnostic scripts
│   ├── database_setup.php    # Database installation
│   ├── database_schema.sql   # Database schema
│   ├── setup.php            # Setup wizard
│   └── diagnose.php         # System diagnostics
├── .htaccess         # Apache configuration and security rules
└── README.md         # Documentation
```

### Design Principles:
- **Separation of Concerns**: Configuration, views, and setup scripts are separated
- **Security**: Sensitive files are protected via `.htaccess` rules
- **Maintainability**: Organized structure makes it easy to locate and modify files
- **Scalability**: Clear structure supports future feature additions

## 🚀 Features

### Core Features (MVP)
- **Secure Login System** with role-based access control
- **Patient Record Management** with stable database operations
- **Appointment Scheduling** with doctor assignment
- **Billing System** with receipt generation and transaction tracking

### Advanced Features
- **Excel Export** for transactions, billing, and cash counts
- **Database Backup** system with automated scheduling
- **Audit Logging** for security and compliance
- **Role-based Access Control** (Doctor, Secretary, Developer, Accountant)

## 👥 User Roles & Permissions

### Doctor
- View and manage patient records
- Schedule and manage appointments
- View billing information
- Access patient medical history

### Secretary
- Create and manage patient records
- Schedule appointments
- Process billing and transactions
- Generate receipts

### Developer
- Full system access
- User management
- Database backup and maintenance
- System configuration

### Accountant
- View financial reports
- Export transaction data
- Monitor cashflow
- Access billing information

## 🛠️ Installation & Setup

### Prerequisites
- XAMPP (Apache, MySQL, PHP)
- Web browser
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Installation Steps

1. **Clone/Download the project**
   ```bash
   # Place the Patient_DBMS folder in your XAMPP htdocs directory
   # Path: C:\xampp\htdocs\Patient_DBMS
   ```

2. **Start XAMPP Services**
   - Start Apache
   - Start MySQL

3. **Setup Database**
   - Open your browser and navigate to: `http://localhost/Patient_DBMS/setup/database_setup.php`
   - This will create the database and all required tables
   - Default users will be created automatically

4. **Access the System**
   - Navigate to: `http://localhost/Patient_DBMS/` or `http://localhost/Patient_DBMS/public/login.php`
   - Use default credentials (see below)

### Default Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Developer | admin | admin123 |
| Doctor | doctor1 | admin123 |
| Secretary | secretary1 | admin123 |
| Accountant | accountant1 | admin123 |

**⚠️ Important: Change these default passwords immediately after first login!**

## 📊 Database Schema

### Core Tables

#### Users Table
```sql
- id (Primary Key)
- username (Unique)
- password (Hashed)
- email
- role (doctor, secretary, developer, accountant)
- full_name
- created_at, updated_at
- is_active
```

#### Patients Table
```sql
- id (Primary Key)
- patient_id (Unique)
- first_name, last_name
- date_of_birth, gender
- phone, email, address
- emergency_contact, emergency_phone
- medical_history, allergies
- insurance_provider, insurance_number
- created_at, updated_at
- created_by (Foreign Key to Users)
- is_active
```

#### Appointments Table
```sql
- id (Primary Key)
- appointment_id (Unique)
- patient_id (Foreign Key)
- doctor_id (Foreign Key)
- appointment_date, appointment_time
- appointment_type
- status (Scheduled, Completed, Cancelled, No-show)
- notes
- created_at, updated_at
- created_by (Foreign Key to Users)
```

#### Billing Table
```sql
- id (Primary Key)
- bill_id (Unique)
- patient_id (Foreign Key)
- appointment_id (Foreign Key)
- total_amount, paid_amount, balance
- payment_status (Pending, Partial, Paid, Overdue)
- billing_date, due_date
- notes
- created_at, updated_at
- created_by (Foreign Key to Users)
```

#### Transactions Table
```sql
- id (Primary Key)
- transaction_id (Unique)
- billing_id (Foreign Key)
- amount
- payment_method (Cash, Credit Card, etc.)
- payment_date
- reference_number
- notes
- created_by (Foreign Key to Users)
```

#### Audit Log Table
```sql
- id (Primary Key)
- user_id (Foreign Key)
- action (CREATE, UPDATE, DELETE, LOGIN, LOGOUT)
- table_name
- record_id
- old_values, new_values (JSON)
- ip_address, user_agent
- created_at
```

## 🔒 Security Features

### Authentication & Authorization
- **Password Hashing**: All passwords are hashed using PHP's `password_hash()` function
- **Session Management**: Secure session handling with proper timeout
- **Role-based Access**: Different permissions for different user roles
- **Input Validation**: All user inputs are validated and sanitized

### Data Protection
- **SQL Injection Prevention**: Prepared statements used throughout
- **XSS Protection**: HTML entities encoding for output
- **CSRF Protection**: Form tokens for state-changing operations
- **Audit Logging**: All user actions are logged for security

### Database Security
- **Soft Deletes**: Records are marked as inactive instead of hard deletion
- **Backup System**: Automated database backups
- **Connection Security**: Secure database connections with error handling

## 📚 Learning PHP & SQL with This Project

### PHP Concepts Demonstrated

#### 1. **Object-Oriented Programming**
```php
// Database connection using mysqli object
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Error handling
if($mysqli === false){
    die("ERROR: Could not connect. " . $mysqli->connect_error);
}
```

#### 2. **Session Management**
```php
// Starting session
session_start();

// Storing user data in session
$_SESSION["user_id"] = $id;
$_SESSION["username"] = $username;
$_SESSION["user_role"] = $role;

// Checking if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}
```

#### 3. **Form Handling & Validation**
```php
// Processing POST data
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate input
    $input_name = trim($_POST["name"]);
    if(empty($input_name)){
        $name_err = "Please enter a name.";
    } else{
        $name = $input_name;
    }
}
```

#### 4. **Prepared Statements (SQL Injection Prevention)**
```php
// Prepare statement
$sql = "SELECT * FROM users WHERE username = ? AND is_active = 1";
if($stmt = $mysqli->prepare($sql)){
    // Bind parameters
    $stmt->bind_param("s", $param_username);
    $param_username = $username;
    
    // Execute
    $stmt->execute();
    $result = $stmt->get_result();
}
```

#### 5. **Password Security**
```php
// Hashing password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verifying password
if(password_verify($password, $hashed_password)){
    // Login successful
}
```

### SQL Concepts Demonstrated

#### 1. **Database Design**
```sql
-- Creating tables with proper relationships
CREATE TABLE patients (
    id INT(11) NOT NULL AUTO_INCREMENT,
    patient_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    -- ... other fields
    created_by INT(11),
    PRIMARY KEY (id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### 2. **Complex Queries with JOINs**
```sql
-- Getting appointments with patient and doctor information
SELECT a.*, p.first_name, p.last_name, u.full_name as doctor_name 
FROM appointments a 
JOIN patients p ON a.patient_id = p.id 
JOIN users u ON a.doctor_id = u.id 
WHERE p.is_active = 1 
ORDER BY a.appointment_date DESC;
```

#### 3. **Aggregate Functions**
```sql
-- Getting statistics for dashboard
SELECT COUNT(*) as total FROM patients WHERE is_active = 1;
SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE DATE(payment_date) = CURDATE();
```

#### 4. **Data Integrity**
```sql
-- Using transactions for data consistency
START TRANSACTION;
INSERT INTO billing (bill_id, patient_id, total_amount) VALUES (?, ?, ?);
INSERT INTO billing_items (billing_id, service_id, quantity, unit_price) VALUES (?, ?, ?, ?);
COMMIT;
```

## 🎯 Key Learning Points

### For Beginners
1. **Start with the basics**: Understand how PHP handles forms and database connections
2. **Security first**: Always validate input and use prepared statements
3. **User experience**: Focus on clear navigation and error handling
4. **Database design**: Learn about relationships and normalization

### For Intermediate Developers
1. **Code organization**: Notice how functions are used for reusability
2. **Error handling**: Study the comprehensive error handling throughout
3. **Security patterns**: Understand the authentication and authorization flow
4. **Database optimization**: Learn about indexing and query optimization

### For Advanced Developers
1. **Architecture patterns**: Study the MVC-like structure
2. **Security best practices**: Implement additional security measures
3. **Performance optimization**: Add caching and query optimization
4. **Testing**: Implement unit and integration tests

## 🔧 Customization & Extension

### Adding New Features
1. **New User Roles**: Modify the role constants in `includes/config.php`
2. **Additional Tables**: Follow the existing pattern in `setup/database_schema.sql`
3. **New Reports**: Create new PHP files in appropriate `public/views/` subdirectory
4. **API Integration**: Add REST API endpoints for mobile apps

### Styling & UI
- The system uses Bootstrap 4 for responsive design
- Custom CSS can be added to `public/assets/css/`
- Custom JavaScript can be added to `public/assets/js/`
- Font Awesome icons are used throughout for better UX

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check if MySQL is running in XAMPP
   - Verify database credentials in `includes/config.php`
   - Ensure the database exists

2. **Permission Denied Errors**
   - Check file permissions in the project directory
   - Ensure Apache has read access to all files
   - Check `.htaccess` configuration

3. **Session Issues**
   - Clear browser cookies and cache
   - Check PHP session configuration
   - Verify session files are writable

4. **Login Not Working**
   - Verify default credentials
   - Check if users table has data
   - Run `setup/database_setup.php` again if needed

5. **404 Errors After Reorganization**
   - Clear browser cache
   - Verify all file paths are updated correctly
   - Check Apache mod_rewrite is enabled for `.htaccess`

## 📈 Future Enhancements

### Planned Features
- **Mobile App**: React Native or Flutter app
- **Email Notifications**: Appointment reminders and billing notifications
- **Advanced Reporting**: Charts and analytics dashboard
- **Multi-language Support**: Internationalization
- **API Documentation**: Swagger/OpenAPI documentation
- **Automated Testing**: Unit and integration tests

### Performance Improvements
- **Caching**: Redis or Memcached integration
- **Database Optimization**: Query optimization and indexing
- **CDN Integration**: For static assets
- **Load Balancing**: For high-traffic scenarios

## 📞 Support & Contributing

### Getting Help
- Check the troubleshooting section above
- Review the code comments for implementation details
- Study the database schema for data relationships

### Contributing
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is open source and available under the MIT License.

## 🙏 Acknowledgments

- Bootstrap for the responsive UI framework
- Font Awesome for the icon library
- PHP and MySQL communities for excellent documentation
- XAMPP for the development environment

---

**Happy Coding! 🚀**

This system demonstrates real-world PHP and SQL development practices. Use it as a learning tool and foundation for your own projects.
