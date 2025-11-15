<?php
// Include config file
require_once "../../../includes/config.php";

// Check if user is logged in
require_login();

// Define variables and initialize with empty values
$bill_id = $patient_id = $appointment_id = $total_amount = $billing_date = $due_date = $notes = "";
$bill_id_err = $patient_id_err = $total_amount_err = $billing_date_err = "";

// Get patients and appointments for dropdowns
$patients_sql = "SELECT id, patient_id, first_name, last_name FROM patients WHERE is_active = 1 ORDER BY first_name, last_name";
$patients_result = $mysqli->query($patients_sql);

$appointments_sql = "SELECT a.id, a.appointment_id, p.first_name, p.last_name, a.appointment_date 
                     FROM appointments a 
                     JOIN patients p ON a.patient_id = p.id 
                     WHERE a.status = 'Completed' 
                     ORDER BY a.appointment_date DESC";
$appointments_result = $mysqli->query($appointments_sql);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate bill ID
    $input_bill_id = trim($_POST["bill_id"]);
    if(empty($input_bill_id)){
        $bill_id_err = "Please enter a bill ID.";
    } else{
        // Check if bill ID already exists
        $sql = "SELECT id FROM billing WHERE bill_id = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $param_bill_id);
            $param_bill_id = $input_bill_id;
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows > 0){
                    $bill_id_err = "This bill ID already exists.";
                } else{
                    $bill_id = $input_bill_id;
                }
            }
            $stmt->close();
        }
    }
    
    // Validate patient
    $input_patient_id = trim($_POST["patient_id"]);
    if(empty($input_patient_id)){
        $patient_id_err = "Please select a patient.";
    } else{
        $patient_id = $input_patient_id;
    }
    
    // Validate total amount
    $input_total_amount = trim($_POST["total_amount"]);
    if(empty($input_total_amount)){
        $total_amount_err = "Please enter the total amount.";
    } elseif(!is_numeric($input_total_amount) || $input_total_amount <= 0){
        $total_amount_err = "Please enter a valid positive number.";
    } else{
        $total_amount = $input_total_amount;
    }
    
    // Validate billing date
    $input_billing_date = trim($_POST["billing_date"]);
    if(empty($input_billing_date)){
        $billing_date_err = "Please enter a billing date.";
    } elseif(!validate_date($input_billing_date, true)){
        $billing_date_err = "Please enter a valid billing date.";
    } else{
        $billing_date = $input_billing_date;
    }
    
    // Get other form data
    $appointment_id = trim($_POST["appointment_id"]) ?: null;
    $due_date = trim($_POST["due_date"]) ?: null;
    
    // Validate due date if provided
    if(!empty($due_date)){
        if(!validate_date($due_date, true)){
            $billing_date_err = "Please enter a valid due date.";
        } elseif($due_date < $billing_date){
            $billing_date_err = "Due date cannot be before billing date.";
        }
    }
    
    $notes = trim($_POST["notes"]);
    
    // Check input errors before inserting in database
    if(empty($bill_id_err) && empty($patient_id_err) && empty($total_amount_err) && empty($billing_date_err)){
        // Prepare an insert statement
        $sql = "INSERT INTO billing (bill_id, patient_id, appointment_id, total_amount, paid_amount, balance, billing_date, due_date, notes, created_by) VALUES (?, ?, ?, ?, 0, ?, ?, ?, ?, ?)";

        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("siiddsssi", $param_bill_id, $param_patient_id, $param_appointment_id, $param_total_amount, $param_balance, $param_billing_date, $param_due_date, $param_notes, $param_created_by);
            
            // Set parameters
            $param_bill_id = $bill_id;
            $param_patient_id = $patient_id;
            $param_appointment_id = $appointment_id;
            $param_total_amount = $total_amount;
            $param_balance = $total_amount; // Initially, balance equals total amount
            $param_billing_date = $billing_date;
            $param_due_date = $due_date;
            $param_notes = $notes;
            $param_created_by = $_SESSION['user_id'];
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                $insert_id = $mysqli->insert_id;
                
                // Log the action using the helper function
                log_audit($mysqli, $_SESSION['user_id'], 'CREATE', 'billing', $insert_id, null, $_POST);
                
                // Records created successfully. Redirect to landing page
                header("location: ../billing/billing.php");
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Bill - Patient DBMS</title>
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
                        <a class="nav-link" href="../patients/patients.php">
                            <i class="fa fa-users"></i> Patients
                        </a>
                        <a class="nav-link" href="../appointments/appointments.php">
                            <i class="fa fa-calendar"></i> Appointments
                        </a>
                        <a class="nav-link active" href="../billing/billing.php">
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
                            <h4><i class="fa fa-file-text"></i> Create New Bill</h4>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Bill ID *</label>
                                            <input type="text" name="bill_id" class="form-control <?php echo (!empty($bill_id_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $bill_id; ?>" placeholder="e.g., BILL001">
                                            <span class="invalid-feedback"><?php echo $bill_id_err;?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Total Amount *</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">$</span>
                                                </div>
                                                <input type="number" name="total_amount" step="0.01" min="0" class="form-control <?php echo (!empty($total_amount_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $total_amount; ?>" placeholder="0.00">
                                            </div>
                                            <span class="invalid-feedback"><?php echo $total_amount_err;?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Patient *</label>
                                            <select name="patient_id" class="form-control <?php echo (!empty($patient_id_err)) ? 'is-invalid' : ''; ?>">
                                                <option value="">Select Patient</option>
                                                <?php while($patient = $patients_result->fetch_assoc()): ?>
                                                <option value="<?php echo $patient['id']; ?>" <?php echo ($patient_id == $patient['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name'] . ' (' . $patient['patient_id'] . ')'); ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                            <span class="invalid-feedback"><?php echo $patient_id_err;?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Appointment (Optional)</label>
                                            <select name="appointment_id" class="form-control">
                                                <option value="">Select Appointment</option>
                                                <?php while($appointment = $appointments_result->fetch_assoc()): ?>
                                                <option value="<?php echo $appointment['id']; ?>" <?php echo ($appointment_id == $appointment['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($appointment['appointment_id'] . ' - ' . $appointment['first_name'] . ' ' . $appointment['last_name'] . ' (' . date('M d, Y', strtotime($appointment['appointment_date'])) . ')'); ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Billing Date *</label>
                                            <input type="date" name="billing_date" class="form-control <?php echo (!empty($billing_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $billing_date ?: date('Y-m-d'); ?>">
                                            <span class="invalid-feedback"><?php echo $billing_date_err;?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Due Date (Optional)</label>
                                            <input type="date" name="due_date" class="form-control" value="<?php echo $due_date; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="notes" class="form-control" rows="4" placeholder="Additional notes or billing details..."><?php echo $notes; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <input type="submit" class="btn btn-primary" value="Create Bill">
                                    <a href="../billing/billing.php" class="btn btn-secondary ml-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
