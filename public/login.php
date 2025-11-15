<?php
// Include config file
require_once "../includes/config.php";

// Initialize variables
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $login_err = "Invalid request. Please try again.";
    }
    
    // Check if username is empty
    if(empty($login_err) && empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } elseif(empty($login_err)) {
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty($login_err) && empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } elseif(empty($login_err)) {
        $password = trim($_POST["password"]);
    }
    
    // Check rate limiting
    if(empty($login_err) && empty($username_err) && empty($password_err)){
        if(!check_login_attempts($username)){
            $login_err = "Too many failed login attempts. Please try again later.";
        }
    }
    
    // Validate credentials
    if(empty($login_err) && empty($username_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT id, username, password, role, full_name, is_active FROM users WHERE username = ? AND is_active = 1";
        
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->store_result();
                
                // Check if username exists, if yes then verify password
                if($stmt->num_rows == 1){
                    // Bind result variables
                    $stmt->bind_result($id, $username, $hashed_password, $role, $full_name, $is_active);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, session already started in config.php
                            
                            // Regenerate session ID to prevent session fixation
                            session_regenerate_id(true);
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["user_role"] = $role;
                            $_SESSION["full_name"] = $full_name;
                            
                            // Log the login
                            $log_sql = "INSERT INTO audit_log (user_id, action, ip_address, user_agent) VALUES (?, 'LOGIN', ?, ?)";
                            if($log_stmt = $mysqli->prepare($log_sql)){
                                $log_stmt->bind_param("iss", $id, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
                                $log_stmt->execute();
                                $log_stmt->close();
                            }
                            
                            // Redirect user to welcome page
                            header("location: index.php");
                            exit();
                        } else{
                            // Password is not valid, log failed attempt
                            log_failed_login($username);
                            // Display a generic error message
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    // Username doesn't exist, log failed attempt
                    log_failed_login($username);
                    // Display a generic error message
                    $login_err = "Invalid username or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            // Close statement
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Patient DBMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background: linear-gradient(135deg,rgb(102, 126, 234) 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-body {
            padding: 30px;
        }
        .form-control {
            border-radius: 25px;
            border: 2px solid #e1e5e9;
            padding: 12px 20px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container">
                    <div class="login-header">
                        <h2><i class="fa fa-user-md"></i> Patient DBMS</h2>
                        <p class="mb-0">Clinic Management System</p>
                    </div>
                    <div class="login-body">
                        <?php 
                        if(!empty($login_err)){
                            echo '<div class="alert alert-danger">' . $login_err . '</div>';
                        }        
                        ?>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <?php echo csrf_token_field(); ?>
                            <div class="form-group">
                                <label><i class="fa fa-user"></i> Username</label>
                                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                                <span class="invalid-feedback"><?php echo $username_err; ?></span>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fa fa-lock"></i> Password</label>
                                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                                <span class="invalid-feedback"><?php echo $password_err; ?></span>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block btn-login">
                                    <i class="fa fa-sign-in"></i> Login
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Default credentials:<br>
                                Admin: admin / admin123<br>
                                Doctor: doctor1 / admin123<br>
                                Secretary: secretary1 / admin123<br>
                                Accountant: accountant1 / admin123
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>