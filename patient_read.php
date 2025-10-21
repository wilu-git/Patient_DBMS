<?php
// Include config file
require_once "config.php";

// Check if user is logged in
require_login();

// Check existence of id parameter before processing further
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    
    // Prepare a select statement
    $sql = "SELECT p.*, u.full_name as created_by_name FROM patients p 
            LEFT JOIN users u ON p.created_by = u.id 
            WHERE p.id = ? AND p.is_active = 1";
    
    if($stmt = $mysqli->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_id);
        
        // Set parameters
        $param_id = trim($_GET["id"]);
        
        // Attempt to execute the prepared statement
        if($stmt->execute()){
            $result = $stmt->get_result();
            
            if($result->num_rows == 1){
                /* Fetch result row as an associative array. Since the result set
                contains only one row, we don't need to use while loop */
                $row = $result->fetch_array(MYSQLI_ASSOC);
                
                // Retrieve individual field value
                $patient_id = $row["patient_id"];
                $first_name = $row["first_name"];
                $last_name = $row["last_name"];
                $date_of_birth = $row["date_of_birth"];
                $gender = $row["gender"];
                $phone = $row["phone"];
                $email = $row["email"];
                $address = $row["address"];
                $emergency_contact = $row["emergency_contact"];
                $emergency_phone = $row["emergency_phone"];
                $medical_history = $row["medical_history"];
                $allergies = $row["allergies"];
                $insurance_provider = $row["insurance_provider"];
                $insurance_number = $row["insurance_number"];
                $created_by_name = $row["created_by_name"];
                $created_at = $row["created_at"];
                $updated_at = $row["updated_at"];
            } else{
                // URL doesn't contain valid id parameter. Redirect to error page
                header("location: error.php");
                exit();
            }
            
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
     
    // Close statement
    $stmt->close();
    
    // Close connection
    $mysqli->close();
} else{
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Patient - Patient DBMS</title>
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
        .info-label {
            font-weight: bold;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
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
                        <a class="nav-link active" href="patients.php">
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
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="container-fluid py-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Patient Details</h2>
                        <div>
                            <a href="patient_update.php?id=<?php echo trim($_GET["id"]); ?>" class="btn btn-warning">
                                <i class="fa fa-pencil"></i> Edit Patient
                            </a>
                            <a href="patients.php" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Back to Patients
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-user"></i> Personal Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="info-label">Patient ID</label>
                                                <p class="info-value"><?php echo htmlspecialchars($patient_id); ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="info-label">Full Name</label>
                                                <p class="info-value"><?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="info-label">Date of Birth</label>
                                                <p class="info-value"><?php echo date('M d, Y', strtotime($date_of_birth)); ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="info-label">Gender</label>
                                                <p class="info-value"><?php echo htmlspecialchars($gender); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="info-label">Phone</label>
                                                <p class="info-value"><?php echo htmlspecialchars($phone ?: 'Not provided'); ?></p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="info-label">Email</label>
                                                <p class="info-value"><?php echo htmlspecialchars($email ?: 'Not provided'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="info-label">Address</label>
                                        <p class="info-value"><?php echo htmlspecialchars($address ?: 'Not provided'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-info-circle"></i> System Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="info-label">Created By</label>
                                        <p class="info-value"><?php echo htmlspecialchars($created_by_name); ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="info-label">Created Date</label>
                                        <p class="info-value"><?php echo date('M d, Y H:i', strtotime($created_at)); ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="info-label">Last Updated</label>
                                        <p class="info-value"><?php echo date('M d, Y H:i', strtotime($updated_at)); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-phone"></i> Emergency Contact</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="info-label">Emergency Contact</label>
                                        <p class="info-value"><?php echo htmlspecialchars($emergency_contact ?: 'Not provided'); ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="info-label">Emergency Phone</label>
                                        <p class="info-value"><?php echo htmlspecialchars($emergency_phone ?: 'Not provided'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-shield-alt"></i> Insurance Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="info-label">Insurance Provider</label>
                                        <p class="info-value"><?php echo htmlspecialchars($insurance_provider ?: 'Not provided'); ?></p>
                                    </div>
                                    <div class="form-group">
                                        <label class="info-label">Insurance Number</label>
                                        <p class="info-value"><?php echo htmlspecialchars($insurance_number ?: 'Not provided'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-heartbeat"></i> Medical History</h5>
                                </div>
                                <div class="card-body">
                                    <p class="info-value"><?php echo htmlspecialchars($medical_history ?: 'No medical history recorded'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-exclamation-triangle"></i> Allergies</h5>
                                </div>
                                <div class="card-body">
                                    <p class="info-value"><?php echo htmlspecialchars($allergies ?: 'No known allergies'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
