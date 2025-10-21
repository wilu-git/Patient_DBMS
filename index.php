<?php
// Include config file
require_once "config.php";

// Check if user is logged in
require_login();

// Get user role for dashboard customization
$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['full_name'];

// Get dashboard statistics based on user role
$stats = [];

// Get total patients
$sql = "SELECT COUNT(*) as total FROM patients WHERE is_active = 1";
$result = $mysqli->query($sql);
$stats['total_patients'] = $result->fetch_assoc()['total'];

// Get today's appointments
$sql = "SELECT COUNT(*) as total FROM appointments WHERE appointment_date = CURDATE() AND status = 'Scheduled'";
$result = $mysqli->query($sql);
$stats['today_appointments'] = $result->fetch_assoc()['total'];

// Get pending bills
$sql = "SELECT COUNT(*) as total FROM billing WHERE payment_status IN ('Pending', 'Partial')";
$result = $mysqli->query($sql);
$stats['pending_bills'] = $result->fetch_assoc()['total'];

// Get today's revenue
$sql = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE DATE(payment_date) = CURDATE()";
$result = $mysqli->query($sql);
$stats['today_revenue'] = $result->fetch_assoc()['total'];

// Get recent appointments
$sql = "SELECT a.*, p.first_name, p.last_name, u.full_name as doctor_name 
        FROM appointments a 
        JOIN patients p ON a.patient_id = p.id 
        JOIN users u ON a.doctor_id = u.id 
        WHERE a.appointment_date >= CURDATE() 
        ORDER BY a.appointment_date, a.appointment_time 
        LIMIT 5";
$recent_appointments = $mysqli->query($sql);

// Get recent transactions
$sql = "SELECT t.*, p.first_name, p.last_name, b.bill_id 
        FROM transactions t 
        JOIN billing b ON t.billing_id = b.id 
        JOIN patients p ON b.patient_id = p.id 
        ORDER BY t.payment_date DESC 
        LIMIT 5";
$recent_transactions = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Patient DBMS</title>
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
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card .card-body {
            padding: 1.5rem;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
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
                        <i class="fa fa-user"></i> <?php echo htmlspecialchars($user_name); ?> (<?php echo ucfirst($user_role); ?>)
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
                        <a class="nav-link active" href="index.php">
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
                        <?php if(in_array($user_role, [ROLE_ACCOUNTANT, ROLE_DEVELOPER])): ?>
                        <a class="nav-link" href="reports.php">
                            <i class="fa fa-chart-bar"></i> Reports
                        </a>
                        <?php endif; ?>
                        <?php if($user_role == ROLE_DEVELOPER): ?>
                        <a class="nav-link" href="users.php">
                            <i class="fa fa-users-cog"></i> User Management
                        </a>
                        <a class="nav-link" href="backup.php">
                            <i class="fa fa-database"></i> Backup
                        </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10">
                <div class="container-fluid py-4">
                    <h2>Dashboard</h2>
                    <p class="text-muted">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</p>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fa fa-users fa-2x mb-2"></i>
                                    <div class="stat-number"><?php echo $stats['total_patients']; ?></div>
                                    <div>Total Patients</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fa fa-calendar fa-2x mb-2"></i>
                                    <div class="stat-number"><?php echo $stats['today_appointments']; ?></div>
                                    <div>Today's Appointments</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fa fa-file-text fa-2x mb-2"></i>
                                    <div class="stat-number"><?php echo $stats['pending_bills']; ?></div>
                                    <div>Pending Bills</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <i class="fa fa-dollar-sign fa-2x mb-2"></i>
                                    <div class="stat-number">$<?php echo number_format($stats['today_revenue'], 2); ?></div>
                                    <div>Today's Revenue</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Recent Appointments -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-calendar"></i> Recent Appointments</h5>
                                </div>
                                <div class="card-body">
                                    <?php if($recent_appointments->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Patient</th>
                                                        <th>Doctor</th>
                                                        <th>Date</th>
                                                        <th>Time</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while($row = $recent_appointments->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></td>
                                                        <td><?php echo date('H:i', strtotime($row['appointment_time'])); ?></td>
                                                        <td>
                                                            <span class="badge badge-<?php echo $row['status'] == 'Scheduled' ? 'primary' : ($row['status'] == 'Completed' ? 'success' : 'warning'); ?>">
                                                                <?php echo $row['status']; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No recent appointments found.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Transactions -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-money"></i> Recent Transactions</h5>
                                </div>
                                <div class="card-body">
                                    <?php if($recent_transactions->num_rows > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Patient</th>
                                                        <th>Amount</th>
                                                        <th>Method</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while($row = $recent_transactions->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                                        <td>$<?php echo number_format($row['amount'], 2); ?></td>
                                                        <td><?php echo $row['payment_method']; ?></td>
                                                        <td><?php echo date('M d, H:i', strtotime($row['payment_date'])); ?></td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No recent transactions found.</p>
                                    <?php endif; ?>
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
