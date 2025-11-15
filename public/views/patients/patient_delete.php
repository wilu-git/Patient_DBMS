<?php
// Include config file
require_once "../../../includes/config.php";

// Check if user is logged in
require_login();

// Check if user has permission to delete patients
require_role([ROLE_DOCTOR, ROLE_SECRETARY, ROLE_DEVELOPER]);

// Process delete operation after confirmation
if(isset($_POST["id"]) && !empty($_POST["id"])){
    
    $param_id = trim($_POST["id"]);
    
    // Check if patient has related records (appointments, billing)
    $check_sql = "SELECT 
                    (SELECT COUNT(*) FROM appointments WHERE patient_id = ? AND status != 'Cancelled') as active_appointments,
                    (SELECT COUNT(*) FROM billing WHERE patient_id = ? AND payment_status != 'Paid') as unpaid_bills";
    
    if($check_stmt = $mysqli->prepare($check_sql)){
        $check_stmt->bind_param("ii", $param_id, $param_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $check_data = $check_result->fetch_assoc();
        $check_stmt->close();
        
        if($check_data['active_appointments'] > 0 || $check_data['unpaid_bills'] > 0){
            $error_msg = "Cannot delete patient. ";
            if($check_data['active_appointments'] > 0){
                $error_msg .= "Patient has {$check_data['active_appointments']} active appointment(s). ";
            }
            if($check_data['unpaid_bills'] > 0){
                $error_msg .= "Patient has {$check_data['unpaid_bills']} unpaid bill(s). ";
            }
            $error_msg .= "Please resolve these before deleting.";
        }
    }
    
    if(!isset($error_msg)){
        // Prepare a soft delete statement (set is_active to 0 instead of hard delete)
        $sql = "UPDATE patients SET is_active = 0 WHERE id = ?";
        
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("i", $param_id);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Log the action using the helper function
                log_audit($mysqli, $_SESSION['user_id'], 'DELETE', 'patients', $param_id);
                
                // Records deleted successfully. Redirect to landing page
                header("location: ../patients/patients.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        $stmt->close();
    }
    
    // Close connection
    $mysqli->close();
} else{
    // Check existence of id parameter
    if(empty(trim($_GET["id"]))){
        // URL doesn't contain id parameter. Redirect to error page
        header("location: ../../error.php");
        exit();
    }
    
    // Get patient details for confirmation
    $sql = "SELECT patient_id, first_name, last_name FROM patients WHERE id = ? AND is_active = 1";
    if($stmt = $mysqli->prepare($sql)){
        $stmt->bind_param("i", $param_id);
        $param_id = trim($_GET["id"]);
        if($stmt->execute()){
            $result = $stmt->get_result();
            if($result->num_rows == 1){
                $patient = $result->fetch_assoc();
            } else{
                header("location: ../../error.php");
                exit();
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Patient - Patient DBMS</title>
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
            <a class="navbar-brand" href="../../index.php">
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
                        <a class="dropdown-item" href="../../logout.php"><i class="fa fa-sign-out"></i> Logout</a>
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
                        <a class="nav-link" href="../../index.php">
                            <i class="fa fa-dashboard"></i> Dashboard
                        </a>
                        <a class="nav-link active" href="../patients/patients.php">
                            <i class="fa fa-users"></i> Patients
                        </a>
                        <a class="nav-link" href="../appointments/appointments.php">
                            <i class="fa fa-calendar"></i> Appointments
                        </a>
                        <a class="nav-link" href="../billing/billing.php">
                            <i class="fa fa-file-text"></i> Billing
                        </a>
                        <a class="nav-link" href="../transactions/transactions.php">
                            <i class="fa fa-money"></i> Transactions
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="container-fluid py-4">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fa fa-trash"></i> Delete Patient</h4>
                        </div>
                        <div class="card-body">
                            <?php if(isset($error_msg)): ?>
                            <div class="alert alert-danger">
                                <h5><i class="fa fa-exclamation-circle"></i> Cannot Delete Patient</h5>
                                <p><?php echo htmlspecialchars($error_msg); ?></p>
                                <a href="../patients/patients.php" class="btn btn-secondary">Back to Patients</a>
                            </div>
                            <?php else: ?>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="alert alert-danger">
                                    <input type="hidden" name="id" value="<?php echo trim($_GET["id"]); ?>"/>
                                    <h5><i class="fa fa-exclamation-triangle"></i> Are you sure you want to delete this patient record?</h5>
                                    <p><strong>Patient ID:</strong> <?php echo htmlspecialchars($patient['patient_id']); ?></p>
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></p>
                                    <p class="mb-0">This action will deactivate the patient record. The patient will no longer appear in the active patients list, but the record will be preserved for audit purposes.</p>
                                    <hr>
                                    <p>
                                        <input type="submit" value="Yes, Delete Patient" class="btn btn-danger">
                                        <a href="../patients/patients.php" class="btn btn-secondary ml-2">No, Cancel</a>
                                    </p>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
