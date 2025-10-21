<?php
// Include config file
require_once "config.php";

// Check if user is logged in and has developer role
require_role([ROLE_DEVELOPER]);

// Function to create database backup
function createBackup($mysqli, $database_name) {
    $backup_dir = "backups/";
    
    // Create backups directory if it doesn't exist
    if (!file_exists($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d_H-i-s');
    $backup_file = $backup_dir . "patient_dbms_backup_" . $timestamp . ".sql";
    
    // Get all table names
    $tables = array();
    $result = $mysqli->query("SHOW TABLES");
    while($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    $sql = "-- Patient DBMS Database Backup\n";
    $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- Database: " . $database_name . "\n\n";
    $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $sql .= "SET AUTOCOMMIT = 0;\n";
    $sql .= "START TRANSACTION;\n";
    $sql .= "SET time_zone = \"+00:00\";\n\n";
    
    // Loop through tables
    foreach($tables as $table) {
        $sql .= "-- Table structure for table `$table`\n";
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        
        $create_table = $mysqli->query("SHOW CREATE TABLE `$table`");
        $create_table_row = $create_table->fetch_array();
        $sql .= $create_table_row[1] . ";\n\n";
        
        // Get table data
        $result = $mysqli->query("SELECT * FROM `$table`");
        if($result->num_rows > 0) {
            $sql .= "-- Data for table `$table`\n";
            
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $sql .= "INSERT INTO `$table` VALUES(";
                $values = array();
                foreach($row as $value) {
                    if($value === NULL) {
                        $values[] = "NULL";
                    } else {
                        $values[] = "'" . $mysqli->real_escape_string($value) . "'";
                    }
                }
                $sql .= implode(", ", $values) . ");\n";
            }
            $sql .= "\n";
        }
    }
    
    $sql .= "COMMIT;\n";
    
    // Write to file
    if(file_put_contents($backup_file, $sql)) {
        return $backup_file;
    } else {
        return false;
    }
}

// Function to restore database from backup
function restoreBackup($mysqli, $backup_file) {
    if(!file_exists($backup_file)) {
        return false;
    }
    
    $sql = file_get_contents($backup_file);
    if($mysqli->multi_query($sql)) {
        // Clear any remaining results
        while($mysqli->next_result()) {
            if($result = $mysqli->store_result()) {
                $result->free();
            }
        }
        return true;
    } else {
        return false;
    }
}

// Handle backup creation
if(isset($_POST['create_backup'])) {
    $backup_file = createBackup($mysqli, DB_NAME);
    if($backup_file) {
        $success_message = "Backup created successfully: " . basename($backup_file);
        
        // Log the action
        $log_sql = "INSERT INTO audit_log (user_id, action, notes) VALUES (?, 'BACKUP_CREATE', ?)";
        if($log_stmt = $mysqli->prepare($log_sql)){
            $log_stmt->bind_param("is", $_SESSION['user_id'], $backup_file);
            $log_stmt->execute();
            $log_stmt->close();
        }
    } else {
        $error_message = "Failed to create backup. Please check file permissions.";
    }
}

// Handle backup restoration
if(isset($_POST['restore_backup']) && isset($_POST['backup_file'])) {
    $backup_file = "backups/" . $_POST['backup_file'];
    if(restoreBackup($mysqli, $backup_file)) {
        $success_message = "Database restored successfully from: " . $_POST['backup_file'];
        
        // Log the action
        $log_sql = "INSERT INTO audit_log (user_id, action, notes) VALUES (?, 'BACKUP_RESTORE', ?)";
        if($log_stmt = $mysqli->prepare($log_sql)){
            $log_stmt->bind_param("is", $_SESSION['user_id'], $backup_file);
            $log_stmt->execute();
            $log_stmt->close();
        }
    } else {
        $error_message = "Failed to restore backup. Please check the backup file.";
    }
}

// Get list of existing backups
$backup_files = array();
if(file_exists("backups/")) {
    $files = scandir("backups/");
    foreach($files as $file) {
        if(pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
            $backup_files[] = $file;
        }
    }
    rsort($backup_files); // Sort by newest first
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Backup - Patient DBMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .navbar-brand {
            font-weight: bold;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .sidebar {
            background: white;
            min-height: calc(100vh - 56px);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: #333;
            padding: 12px 20px;
            border-radius: 5px;
            margin: 2px 10px;
        }
        .sidebar .nav-link:hover {
            background-color: #f8f9fa;
            color: #667eea;
        }
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fa fa-user-md"></i> Patient DBMS
            </a>
            <div class="navbar-nav ml-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                        <i class="fa fa-user"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="profile.php"><i class="fa fa-user"></i> Profile</a>
                        <a class="dropdown-item" href="change_password.php"><i class="fa fa-key"></i> Change Password</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <nav class="nav flex-column">
                        <a class="nav-link" href="index.php">
                            <i class="fa fa-dashboard"></i> Dashboard
                        </a>
                        <a class="nav-link" href="patients.php">
                            <i class="fa fa-users"></i> Patients
                        </a>
                        <a class="nav-link" href="appointments.php">
                            <i class="fa fa-calendar"></i> Appointments
                        </a>
                        <a class="nav-link" href="billing.php">
                            <i class="fa fa-file-text"></i> Billing
                        </a>
                        <a class="nav-link" href="transactions.php">
                            <i class="fa fa-money"></i> Transactions
                        </a>
                        <a class="nav-link" href="reports.php">
                            <i class="fa fa-chart-bar"></i> Reports
                        </a>
                        <a class="nav-link" href="users.php">
                            <i class="fa fa-users-cog"></i> User Management
                        </a>
                        <a class="nav-link active" href="backup.php">
                            <i class="fa fa-database"></i> Backup
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="container-fluid py-4">
                    <h2>Database Backup & Restore</h2>
                    <p class="text-muted">Manage database backups and restore from previous backups.</p>

                    <?php if(isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa fa-check-circle"></i> <?php echo $success_message; ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <?php if(isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa fa-exclamation-circle"></i> <?php echo $error_message; ?>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Create Backup -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-download"></i> Create New Backup</h5>
                                </div>
                                <div class="card-body">
                                    <p>Create a complete backup of the database including all tables and data.</p>
                                    <form method="post">
                                        <button type="submit" name="create_backup" class="btn btn-primary">
                                            <i class="fa fa-download"></i> Create Backup Now
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Restore Backup -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-upload"></i> Restore from Backup</h5>
                                </div>
                                <div class="card-body">
                                    <p>Restore the database from a previous backup. <strong>Warning: This will overwrite all current data!</strong></p>
                                    <form method="post" onsubmit="return confirm('Are you sure you want to restore from this backup? This will overwrite all current data!');">
                                        <div class="form-group">
                                            <label>Select Backup File:</label>
                                            <select name="backup_file" class="form-control" required>
                                                <option value="">Choose a backup file...</option>
                                                <?php foreach($backup_files as $file): ?>
                                                <option value="<?php echo htmlspecialchars($file); ?>">
                                                    <?php echo htmlspecialchars($file); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="submit" name="restore_backup" class="btn btn-warning">
                                            <i class="fa fa-upload"></i> Restore Backup
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Backups -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-archive"></i> Existing Backups</h5>
                                </div>
                                <div class="card-body">
                                    <?php if(count($backup_files) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>File Name</th>
                                                    <th>Size</th>
                                                    <th>Created</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($backup_files as $file): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($file); ?></td>
                                                    <td><?php echo number_format(filesize("backups/" . $file) / 1024, 2); ?> KB</td>
                                                    <td><?php echo date('Y-m-d H:i:s', filemtime("backups/" . $file)); ?></td>
                                                    <td>
                                                        <a href="backups/<?php echo htmlspecialchars($file); ?>" class="btn btn-sm btn-info" download>
                                                            <i class="fa fa-download"></i> Download
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php else: ?>
                                    <p class="text-muted">No backup files found.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Backup Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-info-circle"></i> Backup Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>What's Included in Backups:</h6>
                                            <ul>
                                                <li>All table structures</li>
                                                <li>All patient data</li>
                                                <li>All appointment records</li>
                                                <li>All billing and transaction data</li>
                                                <li>User accounts and permissions</li>
                                                <li>Audit logs</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Best Practices:</h6>
                                            <ul>
                                                <li>Create backups before major updates</li>
                                                <li>Store backups in a secure location</li>
                                                <li>Test restore procedures regularly</li>
                                                <li>Keep multiple backup versions</li>
                                                <li>Document backup and restore procedures</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
