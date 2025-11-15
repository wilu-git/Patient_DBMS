<?php
/**
 * Template Helper Functions
 * Reduces code duplication across pages
 */

// Render the navigation bar
function render_navbar($user_name, $user_role, $base_path = '') {
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo $base_path; ?>index.php">
                <i class="fa fa-user-md"></i> Patient DBMS
            </a>
            <div class="navbar-nav ml-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                        <i class="fa fa-user"></i> <?php echo htmlspecialchars($user_name); ?> (<?php echo ucfirst($user_role); ?>)
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="<?php echo $base_path; ?>profile.php"><i class="fa fa-user"></i> Profile</a>
                        <a class="dropdown-item" href="<?php echo $base_path; ?>change_password.php"><i class="fa fa-key"></i> Change Password</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?php echo $base_path; ?>logout.php"><i class="fa fa-sign-out"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <?php
}

// Render the sidebar
function render_sidebar($current_page, $user_role, $base_path = '') {
    ?>
    <div class="sidebar">
        <nav class="nav flex-column">
            <a class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>index.php">
                <i class="fa fa-dashboard"></i> Dashboard
            </a>
            <a class="nav-link <?php echo ($current_page == 'patients') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>views/patients/patients.php">
                <i class="fa fa-users"></i> Patients
            </a>
            <a class="nav-link <?php echo ($current_page == 'appointments') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>views/appointments/appointments.php">
                <i class="fa fa-calendar"></i> Appointments
            </a>
            <a class="nav-link <?php echo ($current_page == 'billing') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>views/billing/billing.php">
                <i class="fa fa-file-text"></i> Billing
            </a>
            <a class="nav-link <?php echo ($current_page == 'transactions') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>views/transactions/transactions.php">
                <i class="fa fa-money"></i> Transactions
            </a>
            <?php if(in_array($user_role, [ROLE_ACCOUNTANT, ROLE_DEVELOPER])): ?>
            <a class="nav-link <?php echo ($current_page == 'reports') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>reports.php">
                <i class="fa fa-chart-bar"></i> Reports
            </a>
            <?php endif; ?>
            <?php if($user_role == ROLE_DEVELOPER): ?>
            <a class="nav-link <?php echo ($current_page == 'users') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>users.php">
                <i class="fa fa-users-cog"></i> User Management
            </a>
            <a class="nav-link <?php echo ($current_page == 'backup') ? 'active' : ''; ?>" href="<?php echo $base_path; ?>backup.php">
                <i class="fa fa-database"></i> Backup
            </a>
            <?php endif; ?>
        </nav>
    </div>
    <?php
}

// Common page styles
function render_common_styles() {
    ?>
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
    <?php
}
?>
