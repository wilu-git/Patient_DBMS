<?php
// Include config file
require_once "config.php";

// Check if user is logged in
require_login();

// Define variables and initialize with empty values
$appointment_id = $patient_id = $doctor_id = $appointment_date = $appointment_time = $appointment_type = $notes = "";
$appointment_id_err = $patient_id_err = $doctor_id_err = $appointment_date_err = $appointment_time_err = $appointment_type_err = "";

// Get patients and doctors for dropdowns
$patients_sql = "SELECT id, patient_id, first_name, last_name FROM patients WHERE is_active = 1 ORDER BY first_name, last_name";
$patients_result = $mysqli->query($patients_sql);

$doctors_sql = "SELECT id, full_name FROM users WHERE role = 'doctor' AND is_active = 1 ORDER BY full_name";
$doctors_result = $mysqli->query($doctors_sql);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate appointment ID
    $input_appointment_id = trim($_POST["appointment_id"]);
    if(empty($input_appointment_id)){
        $appointment_id_err = "Please enter an appointment ID.";
    } else{
        // Check if appointment ID already exists
        $sql = "SELECT id FROM appointments WHERE appointment_id = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $param_appointment_id);
            $param_appointment_id = $input_appointment_id;
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows > 0){
                    $appointment_id_err = "This appointment ID already exists.";
                } else{
                    $appointment_id = $input_appointment_id;
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
    
    // Validate doctor
    $input_doctor_id = trim($_POST["doctor_id"]);
    if(empty($input_doctor_id)){
        $doctor_id_err = "Please select a doctor.";
    } else{
        $doctor_id = $input_doctor_id;
    }
    
    // Validate appointment date
    $input_appointment_date = trim($_POST["appointment_date"]);
    if(empty($input_appointment_date)){
        $appointment_date_err = "Please enter an appointment date.";
    } else{
        $appointment_date = $input_appointment_date;
    }
    
    // Validate appointment time
    $input_appointment_time = trim($_POST["appointment_time"]);
    if(empty($input_appointment_time)){
        $appointment_time_err = "Please enter an appointment time.";
    } else{
        $appointment_time = $input_appointment_time;
    }
    
    // Validate appointment type
    $input_appointment_type = trim($_POST["appointment_type"]);
    if(empty($input_appointment_type)){
        $appointment_type_err = "Please select an appointment type.";
    } else{
        $appointment_type = $input_appointment_type;
    }
    
    // Get notes
    $notes = trim($_POST["notes"]);
    
    // Check input errors before inserting in database
    if(empty($appointment_id_err) && empty($patient_id_err) && empty($doctor_id_err) && empty($appointment_date_err) && empty($appointment_time_err) && empty($appointment_type_err)){
        // Prepare an insert statement
        $sql = "INSERT INTO appointments (appointment_id, patient_id, doctor_id, appointment_date, appointment_time, appointment_type, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("siissssi", $param_appointment_id, $param_patient_id, $param_doctor_id, $param_appointment_date, $param_appointment_time, $param_appointment_type, $param_notes, $param_created_by);
            
            // Set parameters
            $param_appointment_id = $appointment_id;
            $param_patient_id = $patient_id;
            $param_doctor_id = $doctor_id;
            $param_appointment_date = $appointment_date;
            $param_appointment_time = $appointment_time;
            $param_appointment_type = $appointment_type;
            $param_notes = $notes;
            $param_created_by = $_SESSION['user_id'];
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Log the action
                $log_sql = "INSERT INTO audit_log (user_id, action, table_name, record_id, new_values) VALUES (?, 'CREATE', 'appointments', ?, ?)";
                if($log_stmt = $mysqli->prepare($log_sql)){
                    $log_stmt->bind_param("iis", $_SESSION['user_id'], $mysqli->insert_id, json_encode($_POST));
                    $log_stmt->execute();
                    $log_stmt->close();
                }
                
                // Records created successfully. Redirect to landing page
                header("location: appointments.php");
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
    <title>Create Appointment - Patient DBMS</title>
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
                        <a class="nav-link active" href="appointments.php">
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
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fa fa-calendar-plus"></i> Schedule New Appointment</h4>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Appointment ID *</label>
                                            <input type="text" name="appointment_id" class="form-control <?php echo (!empty($appointment_id_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $appointment_id; ?>" placeholder="e.g., APT001">
                                            <span class="invalid-feedback"><?php echo $appointment_id_err;?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Appointment Type *</label>
                                            <select name="appointment_type" class="form-control <?php echo (!empty($appointment_type_err)) ? 'is-invalid' : ''; ?>">
                                                <option value="">Select Type</option>
                                                <option value="Consultation" <?php echo ($appointment_type == 'Consultation') ? 'selected' : ''; ?>>Consultation</option>
                                                <option value="Follow-up" <?php echo ($appointment_type == 'Follow-up') ? 'selected' : ''; ?>>Follow-up</option>
                                                <option value="Emergency" <?php echo ($appointment_type == 'Emergency') ? 'selected' : ''; ?>>Emergency</option>
                                                <option value="Surgery" <?php echo ($appointment_type == 'Surgery') ? 'selected' : ''; ?>>Surgery</option>
                                                <option value="Check-up" <?php echo ($appointment_type == 'Check-up') ? 'selected' : ''; ?>>Check-up</option>
                                            </select>
                                            <span class="invalid-feedback"><?php echo $appointment_type_err;?></span>
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
                                            <label>Doctor *</label>
                                            <select name="doctor_id" class="form-control <?php echo (!empty($doctor_id_err)) ? 'is-invalid' : ''; ?>">
                                                <option value="">Select Doctor</option>
                                                <?php while($doctor = $doctors_result->fetch_assoc()): ?>
                                                <option value="<?php echo $doctor['id']; ?>" <?php echo ($doctor_id == $doctor['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($doctor['full_name']); ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                            <span class="invalid-feedback"><?php echo $doctor_id_err;?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Appointment Date *</label>
                                            <input type="date" name="appointment_date" class="form-control <?php echo (!empty($appointment_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $appointment_date; ?>" min="<?php echo date('Y-m-d'); ?>">
                                            <span class="invalid-feedback"><?php echo $appointment_date_err;?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Appointment Time *</label>
                                            <input type="time" name="appointment_time" class="form-control <?php echo (!empty($appointment_time_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $appointment_time; ?>">
                                            <span class="invalid-feedback"><?php echo $appointment_time_err;?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="notes" class="form-control" rows="4" placeholder="Additional notes or special instructions..."><?php echo $notes; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <input type="submit" class="btn btn-primary" value="Schedule Appointment">
                                    <a href="appointments.php" class="btn btn-secondary ml-2">Cancel</a>
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
