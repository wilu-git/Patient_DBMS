<?php
// Include config file
require_once "../includes/config.php";
require_once "../includes/template_helper.php";

// Check if user is logged in
require_login();

$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['full_name'];
$user_id = $_SESSION['user_id'];

// Get user profile information
$sql = "SELECT username, email, full_name, role, created_at FROM users WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - Patient DBMS</title>
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
                <?php render_sidebar('profile', $user_role); ?>
            </div>

            <div class="col-md-10">
                <div class="container-fluid py-4">
                    <h2>User Profile</h2>
                    <p class="text-muted">View your profile information</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-user"></i> Profile Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th>Username:</th>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Full Name:</th>
                                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Role:</th>
                                            <td>
                                                <span class="badge badge-primary">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Member Since:</th>
                                            <td><?php echo date('F d, Y', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                    </table>
                                    
                                    <div class="mt-4">
                                        <a href="change_password.php" class="btn btn-primary">
                                            <i class="fa fa-key"></i> Change Password
                                        </a>
                                    </div>
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
