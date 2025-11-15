<!-- Sidebar -->
<div class="col-md-2 p-0">
    <div class="sidebar">
        <nav class="nav flex-column">
            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'dashboard') ? 'active' : ''; ?>" 
               href="<?php echo isset($base_url) ? $base_url : '../../'; ?>index.php">
                <i class="fa fa-dashboard"></i> Dashboard
            </a>
            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'patients') ? 'active' : ''; ?>" 
               href="<?php echo isset($base_url) ? $base_url : '../'; ?>patients/patients.php">
                <i class="fa fa-users"></i> Patients
            </a>
            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'appointments') ? 'active' : ''; ?>" 
               href="<?php echo isset($base_url) ? $base_url : '../'; ?>appointments/appointments.php">
                <i class="fa fa-calendar"></i> Appointments
            </a>
            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'billing') ? 'active' : ''; ?>" 
               href="<?php echo isset($base_url) ? $base_url : '../'; ?>billing/billing.php">
                <i class="fa fa-file-text"></i> Billing
            </a>
            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'transactions') ? 'active' : ''; ?>" 
               href="<?php echo isset($base_url) ? $base_url : '../'; ?>transactions/transactions.php">
                <i class="fa fa-money"></i> Transactions
            </a>
            <?php if(isset($user_role) && in_array($user_role, [ROLE_ACCOUNTANT, ROLE_DEVELOPER])): ?>
            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'reports') ? 'active' : ''; ?>" 
               href="<?php echo isset($base_url) ? $base_url : ''; ?>reports.php">
                <i class="fa fa-chart-bar"></i> Reports
            </a>
            <?php endif; ?>
            <?php if(isset($user_role) && $user_role == ROLE_DEVELOPER): ?>
            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'users') ? 'active' : ''; ?>" 
               href="<?php echo isset($base_url) ? $base_url : ''; ?>users.php">
                <i class="fa fa-users-cog"></i> User Management
            </a>
            <a class="nav-link <?php echo (isset($active_page) && $active_page == 'backup') ? 'active' : ''; ?>" 
               href="<?php echo isset($base_url) ? $base_url : '../../'; ?>backup.php">
                <i class="fa fa-database"></i> Backup
            </a>
            <?php endif; ?>
        </nav>
    </div>
</div>
