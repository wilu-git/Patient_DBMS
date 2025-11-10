<?php
// Diagnostic script to check database setup
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient DBMS - System Diagnosis</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .diagnostic-card { margin-bottom: 20px; }
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fa fa-stethoscope"></i> Patient DBMS System Diagnosis</h4>
                    </div>
                    <div class="card-body">
                        
                        <?php
                        echo "<h5>🔍 System Check Results:</h5><hr>";
                        
                        // Check 1: PHP Version
                        echo "<div class='diagnostic-card'>";
                        echo "<h6><i class='fa fa-code'></i> PHP Version</h6>";
                        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
                            echo "<p class='status-ok'><i class='fa fa-check-circle'></i> PHP Version: " . PHP_VERSION . " ✓</p>";
                        } else {
                            echo "<p class='status-error'><i class='fa fa-times-circle'></i> PHP Version: " . PHP_VERSION . " (Requires 7.0+) ✗</p>";
                        }
                        echo "</div>";
                        
                        // Check 2: MySQL Extension
                        echo "<div class='diagnostic-card'>";
                        echo "<h6><i class='fa fa-database'></i> MySQL Extension</h6>";
                        if (extension_loaded('mysqli')) {
                            echo "<p class='status-ok'><i class='fa fa-check-circle'></i> MySQLi extension loaded ✓</p>";
                        } else {
                            echo "<p class='status-error'><i class='fa fa-times-circle'></i> MySQLi extension not loaded ✗</p>";
                        }
                        echo "</div>";
                        
                        // Check 3: Database Connection
                        echo "<div class='diagnostic-card'>";
                        echo "<h6><i class='fa fa-plug'></i> Database Connection</h6>";
                        try {
                            $mysqli = new mysqli('localhost', 'root', '', 'patient_dbms');
                            if ($mysqli->connect_error) {
                                echo "<p class='status-error'><i class='fa fa-times-circle'></i> Connection failed: " . $mysqli->connect_error . " ✗</p>";
                            } else {
                                echo "<p class='status-ok'><i class='fa fa-check-circle'></i> Database connection successful ✓</p>";
                                
                                // Check 4: Database Exists
                                echo "<div class='diagnostic-card'>";
                                echo "<h6><i class='fa fa-database'></i> Database Exists</h6>";
                                $result = $mysqli->query("SHOW DATABASES LIKE 'patient_dbms'");
                                if ($result->num_rows > 0) {
                                    echo "<p class='status-ok'><i class='fa fa-check-circle'></i> Database 'patient_dbms' exists ✓</p>";
                                    
                                    // Check 5: Tables Exist
                                    echo "<div class='diagnostic-card'>";
                                    echo "<h6><i class='fa fa-table'></i> Tables Check</h6>";
                                    $tables = ['users', 'patients', 'appointments', 'services', 'billing', 'billing_items', 'transactions', 'audit_log'];
                                    $all_tables_exist = true;
                                    
                                    foreach ($tables as $table) {
                                        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
                                        if ($result->num_rows > 0) {
                                            echo "<p class='status-ok'><i class='fa fa-check-circle'></i> Table '$table' exists ✓</p>";
                                        } else {
                                            echo "<p class='status-error'><i class='fa fa-times-circle'></i> Table '$table' missing ✗</p>";
                                            $all_tables_exist = false;
                                        }
                                    }
                                    
                                    if ($all_tables_exist) {
                                        // Check 6: Users Data
                                        echo "<div class='diagnostic-card'>";
                                        echo "<h6><i class='fa fa-users'></i> Users Data</h6>";
                                        $result = $mysqli->query("SELECT COUNT(*) as count FROM users");
                                        $row = $result->fetch_assoc();
                                        $user_count = $row['count'];
                                        
                                        if ($user_count > 0) {
                                            echo "<p class='status-ok'><i class='fa fa-check-circle'></i> Found $user_count users in database ✓</p>";
                                            
                                            // Show users
                                            $result = $mysqli->query("SELECT username, role, full_name FROM users");
                                            echo "<table class='table table-sm'>";
                                            echo "<thead><tr><th>Username</th><th>Role</th><th>Full Name</th></tr></thead><tbody>";
                                            while ($user = $result->fetch_assoc()) {
                                                echo "<tr><td>" . htmlspecialchars($user['username']) . "</td><td>" . htmlspecialchars($user['role']) . "</td><td>" . htmlspecialchars($user['full_name']) . "</td></tr>";
                                            }
                                            echo "</tbody></table>";
                                            
                                        } else {
                                            echo "<p class='status-error'><i class='fa fa-times-circle'></i> No users found in database ✗</p>";
                                        }
                                        echo "</div>";
                                    }
                                    
                                } else {
                                    echo "<p class='status-error'><i class='fa fa-times-circle'></i> Database 'patient_dbms' does not exist ✗</p>";
                                }
                                echo "</div>";
                            }
                        } catch (Exception $e) {
                            echo "<p class='status-error'><i class='fa fa-times-circle'></i> Connection error: " . $e->getMessage() . " ✗</p>";
                        }
                        echo "</div>";
                        
                        // Check 7: File Permissions
                        echo "<div class='diagnostic-card'>";
                        echo "<h6><i class='fa fa-file'></i> File Permissions</h6>";
                        $files_to_check = ['config.php', 'login.php', 'database_setup.php'];
                        foreach ($files_to_check as $file) {
                            if (file_exists($file)) {
                                if (is_readable($file)) {
                                    echo "<p class='status-ok'><i class='fa fa-check-circle'></i> $file is readable ✓</p>";
                                } else {
                                    echo "<p class='status-error'><i class='fa fa-times-circle'></i> $file is not readable ✗</p>";
                                }
                            } else {
                                echo "<p class='status-error'><i class='fa fa-times-circle'></i> $file does not exist ✗</p>";
                            }
                        }
                        echo "</div>";
                        ?>
                        
                        <hr>
                        
                        <div class="alert alert-info">
                            <h6><i class='fa fa-lightbulb'></i> Quick Fixes:</h6>
                            <ul>
                                <li><strong>If database doesn't exist:</strong> <a href="database_setup.php" class="btn btn-sm btn-primary">Run Database Setup</a></li>
                                <li><strong>If no users found:</strong> <a href="database_setup.php" class="btn btn-sm btn-warning">Recreate Users</a></li>
                                <li><strong>If connection fails:</strong> Make sure XAMPP MySQL is running</li>
                            </ul>
                        </div>
                        
                        <div class="text-center">
                            <a href="../public/login.php" class="btn btn-success">
                                <i class="fa fa-sign-in"></i> Try Login Again
                            </a>
                            <a href="setup.php" class="btn btn-primary">
                                <i class="fa fa-cog"></i> Setup Page
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
