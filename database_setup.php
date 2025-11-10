<?php
/**
 * Database Setup Script
 * This script creates the database and all required tables with initial data
 */

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'patient_dbms');

// Create connection to MySQL server (without database selected)
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if($mysqli === false){
    die("ERROR: Could not connect to MySQL server. " . $mysqli->connect_error);
}

// Read and execute the database schema
$sql_file = 'database_schema.sql';
if (!file_exists($sql_file)) {
    die("ERROR: Database schema file not found: " . $sql_file);
}

// Read the SQL file
$sql_content = file_get_contents($sql_file);

// Split SQL statements
$sql_statements = array_filter(
    array_map('trim', 
        preg_split('/;(\s*\n|$)/', $sql_content, -1, PREG_SPLIT_NO_EMPTY)
    )
);

$success = true;
$messages = [];

// Execute each statement
foreach ($sql_statements as $statement) {
    // Skip comments and SELECT statements
    if (empty($statement) || 
        strpos($statement, '--') === 0 || 
        strpos($statement, '/*') === 0 ||
        stripos($statement, 'SELECT ') === 0) {
        continue;
    }
    
    if ($mysqli->multi_query($statement . ';')) {
        do {
            // Store first result set
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
        } while ($mysqli->next_result());
        
        $messages[] = "✓ Executed successfully";
    } else {
        $error = $mysqli->error;
        // Ignore "already exists" errors
        if (strpos($error, 'already exists') === false && 
            strpos($error, 'Duplicate entry') === false) {
            $success = false;
            $messages[] = "✗ Error: " . $error;
        } else {
            $messages[] = "⚠ Already exists (skipped)";
        }
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Setup - Patient DBMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        .setup-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .setup-icon {
            font-size: 4rem;
            color: <?php echo $success ? '#28a745' : '#dc3545'; ?>;
            margin-bottom: 20px;
        }
        .message {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            font-family: monospace;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="setup-container">
                    <div class="setup-header">
                        <div class="setup-icon">
                            <i class="fa fa-<?php echo $success ? 'check-circle' : 'times-circle'; ?>"></i>
                        </div>
                        <h2>Database Setup <?php echo $success ? 'Completed' : 'Failed'; ?></h2>
                        <p class="text-muted">Patient DBMS Installation</p>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <h5><i class="fa fa-check"></i> Setup Successful!</h5>
                            <p>The database and all required tables have been created successfully.</p>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fa fa-key"></i> Default Login Credentials:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Developer:</strong> admin / admin123<br>
                                    <strong>Doctor:</strong> doctor1 / admin123
                                </div>
                                <div class="col-md-6">
                                    <strong>Secretary:</strong> secretary1 / admin123<br>
                                    <strong>Accountant:</strong> accountant1 / admin123
                                </div>
                            </div>
                            <p class="mt-2 mb-0"><small><i class="fa fa-exclamation-triangle"></i> Please change these passwords after first login!</small></p>
                        </div>

                        <div class="text-center mt-4">
                            <a href="login.php" class="btn btn-primary btn-lg">
                                <i class="fa fa-sign-in"></i> Go to Login
                            </a>
                            <a href="diagnose.php" class="btn btn-outline-secondary btn-lg ml-2">
                                <i class="fa fa-stethoscope"></i> Run Diagnostics
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <h5><i class="fa fa-times"></i> Setup Failed!</h5>
                            <p>There were errors during the database setup. Please check the details below.</p>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="fa fa-wrench"></i> Troubleshooting:</h6>
                            <ul>
                                <li>Make sure XAMPP MySQL service is running</li>
                                <li>Check database credentials in the configuration</li>
                                <li>Ensure you have proper permissions</li>
                                <li>Try running the setup again</li>
                            </ul>
                        </div>

                        <div class="text-center mt-4">
                            <a href="database_setup.php" class="btn btn-warning btn-lg">
                                <i class="fa fa-refresh"></i> Try Again
                            </a>
                            <a href="diagnose.php" class="btn btn-outline-secondary btn-lg ml-2">
                                <i class="fa fa-stethoscope"></i> Run Diagnostics
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
