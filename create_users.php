<?php
// Manual user creation script
require_once "config.php";

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS patient_dbms CHARACTER SET utf8 COLLATE utf8_general_ci";
if ($mysqli->query($sql) === TRUE) {
    echo "Database created successfully.<br>";
} else {
    echo "Error creating database: " . $mysqli->error . "<br>";
}

// Select the database
$mysqli->select_db("patient_dbms");

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('doctor', 'secretary', 'developer', 'accountant') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

if ($mysqli->query($sql) === TRUE) {
    echo "Users table created successfully.<br>";
} else {
    echo "Error creating users table: " . $mysqli->error . "<br>";
}

// Clear existing users and insert new ones
$sql = "DELETE FROM users";
$mysqli->query($sql);

// Insert default users with hashed passwords
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, password, email, role, full_name) VALUES 
('admin', '$admin_password', 'admin@clinic.com', 'developer', 'System Administrator'),
('doctor1', '$admin_password', 'doctor@clinic.com', 'doctor', 'Dr. John Smith'),
('secretary1', '$admin_password', 'secretary@clinic.com', 'secretary', 'Jane Doe'),
('accountant1', '$admin_password', 'accountant@clinic.com', 'accountant', 'Bob Johnson')";

if ($mysqli->query($sql) === TRUE) {
    echo "<div style='color: green; font-weight: bold;'>✅ Users created successfully!</div>";
    echo "<br><strong>Login Credentials:</strong><br>";
    echo "Username: admin | Password: admin123<br>";
    echo "Username: doctor1 | Password: admin123<br>";
    echo "Username: secretary1 | Password: admin123<br>";
    echo "Username: accountant1 | Password: admin123<br>";
} else {
    echo "Error creating users: " . $mysqli->error . "<br>";
}

// Verify users were created
$sql = "SELECT username, role, full_name FROM users";
$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    echo "<br><strong>Users in database:</strong><br>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Username</th><th>Role</th><th>Full Name</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['username'] . "</td><td>" . $row['role'] . "</td><td>" . $row['full_name'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "No users found in database.";
}

$mysqli->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Creation Complete</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4>✅ User Creation Complete</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <h5>🎉 Success! Users have been created.</h5>
                            <p>You can now login to the system using these credentials:</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6>Developer Access</h6>
                                    </div>
                                    <div class="card-body">
                                        <strong>Username:</strong> admin<br>
                                        <strong>Password:</strong> admin123
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6>Doctor Access</h6>
                                    </div>
                                    <div class="card-body">
                                        <strong>Username:</strong> doctor1<br>
                                        <strong>Password:</strong> admin123
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6>Secretary Access</h6>
                                    </div>
                                    <div class="card-body">
                                        <strong>Username:</strong> secretary1<br>
                                        <strong>Password:</strong> admin123
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6>Accountant Access</h6>
                                    </div>
                                    <div class="card-body">
                                        <strong>Username:</strong> accountant1<br>
                                        <strong>Password:</strong> admin123
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="login.php" class="btn btn-primary btn-lg">
                                <i class="fa fa-sign-in"></i> Go to Login Page
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
