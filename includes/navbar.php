<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo isset($base_url) ? $base_url : '../../'; ?>index.php">
            <i class="fa fa-user-md"></i> Patient DBMS
        </a>
        <div class="navbar-nav ml-auto">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                    <i class="fa fa-user"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?> 
                    <?php if(isset($user_role)): ?>(<?php echo ucfirst($user_role); ?>)<?php endif; ?>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="<?php echo isset($base_url) ? $base_url : '../../'; ?>profile.php"><i class="fa fa-user"></i> Profile</a>
                    <a class="dropdown-item" href="<?php echo isset($base_url) ? $base_url : '../../'; ?>change_password.php"><i class="fa fa-key"></i> Change Password</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?php echo isset($base_url) ? $base_url : '../../'; ?>logout.php"><i class="fa fa-sign-out"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>
