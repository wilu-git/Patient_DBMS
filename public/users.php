<?php
// Include config file
require_once "../includes/config.php";
require_once "../includes/template_helper.php";

// Check if user is logged in and is a developer
require_role([ROLE_DEVELOPER]);

$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['full_name'];

// Get all users
$sql = "SELECT id, username, email, full_name, role, is_active, created_at FROM users ORDER BY created_at DESC";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - Patient DBMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <?php render_common_styles(); ?>
</head>
<body>
    <?php render_navbar($user_name, $user_role); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0">
                <?php render_sidebar('users', $user_role); ?>
            </div>

            <div class="col-md-10">
                <div class="container-fluid py-4">
                    <h2>User Management</h2>
                    <p class="text-muted">Manage system users and their roles</p>

                    <div class="card">
                        <div class="card-body">
                            <?php if($result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Full Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td>
                                                    <span class="badge badge-primary">
                                                        <?php echo ucfirst($row['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($row['is_active']): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" disabled>
                                                        <i class="fa fa-eye"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No users found.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="fa fa-info-circle"></i> 
                        <strong>Note:</strong> Full user management features (create, edit, delete users) are planned for future releases.
                        For now, you can use the <a href="create_users.php">Create Users</a> script or manage users directly in the database.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
