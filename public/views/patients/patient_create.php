<?php
// Include config file
require_once "../../../includes/config.php";

// Check if user is logged in
require_login();

// Define variables and initialize with empty values
$patient_id = $first_name = $last_name = $date_of_birth = $gender = $phone = $email = $address = "";
$emergency_contact = $emergency_phone = $medical_history = $allergies = $insurance_provider = $insurance_number = "";
$patient_id_err = $first_name_err = $last_name_err = $date_of_birth_err = $gender_err = $phone_err = $email_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate patient ID
    $input_patient_id = trim($_POST["patient_id"]);
    if(empty($input_patient_id)){
        $patient_id_err = "Please enter a patient ID.";
    } else{
        // Check if patient ID already exists
        $sql = "SELECT id FROM patients WHERE patient_id = ?";
        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $param_patient_id);
            $param_patient_id = $input_patient_id;
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows > 0){
                    $patient_id_err = "This patient ID already exists.";
                } else{
                    $patient_id = $input_patient_id;
                }
            }
            $stmt->close();
        }
    }
    
    // Validate first name
    $input_first_name = trim($_POST["first_name"]);
    if(empty($input_first_name)){
        $first_name_err = "Please enter a first name.";
    } elseif(!filter_var($input_first_name, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Z\s]+$/")))){
        $first_name_err = "Please enter a valid first name.";
    } else{
        $first_name = $input_first_name;
    }
    
    // Validate last name
    $input_last_name = trim($_POST["last_name"]);
    if(empty($input_last_name)){
        $last_name_err = "Please enter a last name.";
    } elseif(!filter_var($input_last_name, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Z\s]+$/")))){
        $last_name_err = "Please enter a valid last name.";
    } else{
        $last_name = $input_last_name;
    }
    
    // Validate date of birth
    $input_date_of_birth = trim($_POST["date_of_birth"]);
    if(empty($input_date_of_birth)){
        $date_of_birth_err = "Please enter a date of birth.";
    } else{
        $date_of_birth = $input_date_of_birth;
    }
    
    // Validate gender
    $input_gender = trim($_POST["gender"]);
    if(empty($input_gender)){
        $gender_err = "Please select a gender.";
    } else{
        $gender = $input_gender;
    }
    
    // Validate phone
    $input_phone = trim($_POST["phone"]);
    if(!empty($input_phone) && !preg_match("/^[0-9\-\+\(\)\s]+$/", $input_phone)){
        $phone_err = "Please enter a valid phone number.";
    } else{
        $phone = $input_phone;
    }
    
    // Validate email
    $input_email = trim($_POST["email"]);
    if(!empty($input_email) && !filter_var($input_email, FILTER_VALIDATE_EMAIL)){
        $email_err = "Please enter a valid email address.";
    } else{
        $email = $input_email;
    }
    
    // Get other form data
    $address = trim($_POST["address"]);
    $emergency_contact = trim($_POST["emergency_contact"]);
    $emergency_phone = trim($_POST["emergency_phone"]);
    $medical_history = trim($_POST["medical_history"]);
    $allergies = trim($_POST["allergies"]);
    $insurance_provider = trim($_POST["insurance_provider"]);
    $insurance_number = trim($_POST["insurance_number"]);
    
    // Check input errors before inserting in database
    if(empty($patient_id_err) && empty($first_name_err) && empty($last_name_err) && empty($date_of_birth_err) && empty($gender_err) && empty($phone_err) && empty($email_err)){
        // Prepare an insert statement
        $sql = "INSERT INTO patients (patient_id, first_name, last_name, date_of_birth, gender, phone, email, address, emergency_contact, emergency_phone, medical_history, allergies, insurance_provider, insurance_number, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssssssssssssssi", $param_patient_id, $param_first_name, $param_last_name, $param_date_of_birth, $param_gender, $param_phone, $param_email, $param_address, $param_emergency_contact, $param_emergency_phone, $param_medical_history, $param_allergies, $param_insurance_provider, $param_insurance_number, $param_created_by);
            
            // Set parameters
            $param_patient_id = $patient_id;
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_date_of_birth = $date_of_birth;
            $param_gender = $gender;
            $param_phone = $phone;
            $param_email = $email;
            $param_address = $address;
            $param_emergency_contact = $emergency_contact;
            $param_emergency_phone = $emergency_phone;
            $param_medical_history = $medical_history;
            $param_allergies = $allergies;
            $param_insurance_provider = $insurance_provider;
            $param_insurance_number = $insurance_number;
            $param_created_by = $_SESSION['user_id'];
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Log the action
                $log_sql = "INSERT INTO audit_log (user_id, action, table_name, record_id, new_values) VALUES (?, 'CREATE', 'patients', ?, ?)";
                if($log_stmt = $mysqli->prepare($log_sql)){
                    $log_stmt->bind_param("iis", $_SESSION['user_id'], $mysqli->insert_id, json_encode($_POST));
                    $log_stmt->execute();
                    $log_stmt->close();
                }
                
                // Records created successfully. Redirect to landing page
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Patient - Patient DBMS</title>
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
                            <h4><i class="fa fa-user-plus"></i> Create New Patient</h4>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Patient ID *</label>
                                            <input type="text" name="patient_id" class="form-control <?php echo (!empty($patient_id_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $patient_id; ?>" placeholder="e.g., P001">
                                            <span class="invalid-feedback"><?php echo $patient_id_err;?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date of Birth *</label>
                                            <input type="date" name="date_of_birth" class="form-control <?php echo (!empty($date_of_birth_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $date_of_birth; ?>">
                                            <span class="invalid-feedback"><?php echo $date_of_birth_err;?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>First Name *</label>
                                            <input type="text" name="first_name" class="form-control <?php echo (!empty($first_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $first_name; ?>">
                                            <span class="invalid-feedback"><?php echo $first_name_err;?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Last Name *</label>
                                            <input type="text" name="last_name" class="form-control <?php echo (!empty($last_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $last_name; ?>">
                                            <span class="invalid-feedback"><?php echo $last_name_err;?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Gender *</label>
                                            <select name="gender" class="form-control <?php echo (!empty($gender_err)) ? 'is-invalid' : ''; ?>">
                                                <option value="">Select Gender</option>
                                                <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                                                <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                                                <option value="Other" <?php echo ($gender == 'Other') ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                            <span class="invalid-feedback"><?php echo $gender_err;?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Phone</label>
                                            <input type="text" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>" placeholder="e.g., 09123456789">
                                            <span class="invalid-feedback"><?php echo $phone_err;?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                                            <span class="invalid-feedback"><?php echo $email_err;?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Insurance Provider</label>
                                            <input type="text" name="insurance_provider" class="form-control" value="<?php echo $insurance_provider; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea name="address" class="form-control" rows="3"><?php echo $address; ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Emergency Contact</label>
                                            <input type="text" name="emergency_contact" class="form-control" value="<?php echo $emergency_contact; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Emergency Phone</label>
                                            <input type="text" name="emergency_phone" class="form-control" value="<?php echo $emergency_phone; ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Medical History</label>
                                            <textarea name="medical_history" class="form-control" rows="3"><?php echo $medical_history; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Allergies</label>
                                            <textarea name="allergies" class="form-control" rows="3"><?php echo $allergies; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Insurance Number</label>
                                    <input type="text" name="insurance_number" class="form-control" value="<?php echo $insurance_number; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <input type="submit" class="btn btn-primary" value="Create Patient">
                                    <a href="../patients/patients.php" class="btn btn-secondary ml-2">Cancel</a>
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
