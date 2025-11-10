<?php
// Include config file
require_once "../../../includes/config.php";

// Check if user is logged in
require_login();

// Get user role for access control
$user_role = $_SESSION['user_role'];

// Attempt select query execution
$sql = "SELECT p.*, u.full_name as created_by_name FROM patients p 
        LEFT JOIN users u ON p.created_by = u.id 
        WHERE p.is_active = 1 
        ORDER BY p.created_at DESC";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patients - Patient DBMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        .btn-action {
            padding: 5px 10px;
            margin: 2px;
        }
    </style>
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();   
        });
    </script>
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
                        <i class="fa fa-user"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?> (<?php echo ucfirst($user_role); ?>)
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
                        <?php if(in_array($user_role, [ROLE_ACCOUNTANT, ROLE_DEVELOPER])): ?>
                        <a class="nav-link" href="reports.php">
                            <i class="fa fa-chart-bar"></i> Reports
                        </a>
                        <?php endif; ?>
                        <?php if($user_role == ROLE_DEVELOPER): ?>
                        <a class="nav-link" href="users.php">
                            <i class="fa fa-users-cog"></i> User Management
                        </a>
                        <a class="nav-link" href="../../backup.php">
                            <i class="fa fa-database"></i> Backup
                        </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="container-fluid py-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Patient Management</h2>
                        <a href="patient_create.php" class="btn btn-success">
                            <i class="fa fa-plus"></i> Add New Patient
                        </a>
                    </div>

                    <?php
                    if($result->num_rows > 0){
                        echo '<div class="card">';
                        echo '<div class="card-body">';
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-bordered table-striped">';
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th>Patient ID</th>";
                        echo "<th>Name</th>";
                        echo "<th>Date of Birth</th>";
                        echo "<th>Gender</th>";
                        echo "<th>Phone</th>";
                        echo "<th>Email</th>";
                        echo "<th>Created By</th>";
                        echo "<th>Created Date</th>";
                        echo "<th>Action</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";
                        while($row = $result->fetch_array()){
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['patient_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                            echo "<td>" . date('M d, Y', strtotime($row['date_of_birth'])) . "</td>";
                            echo "<td>" . htmlspecialchars($row['gender']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['created_by_name']) . "</td>";
                            echo "<td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>";
                            echo "<td>";
                            echo '<a href="patient_read.php?id='. $row['id'] .'" class="btn btn-info btn-action" title="View Record" data-toggle="tooltip"><span class="fa fa-eye"></span></a>';
                            echo '<a href="patient_update.php?id='. $row['id'] .'" class="btn btn-warning btn-action" title="Update Record" data-toggle="tooltip"><span class="fa fa-pencil"></span></a>';
                            if(in_array($user_role, [ROLE_DOCTOR, ROLE_SECRETARY, ROLE_DEVELOPER])){
                                echo '<a href="patient_delete.php?id='. $row['id'] .'" class="btn btn-danger btn-action" title="Delete Record" data-toggle="tooltip"><span class="fa fa-trash"></span></a>';
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                        echo "</tbody>";                            
                        echo "</table>";
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                        // Free result set
                        $result->free();
                    } else{
                        echo '<div class="alert alert-info"><em>No patient records were found.</em></div>';
                    }
                    
                    // Close connection
                    $mysqli->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
