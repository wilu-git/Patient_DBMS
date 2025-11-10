<?php
// Include config file
require_once "../includes/config.php";

// Check if user is logged in
if (is_logged_in()) {
    // Log the logout
    $log_sql = "INSERT INTO audit_log (user_id, action, ip_address, user_agent) VALUES (?, 'LOGOUT', ?, ?)";
    if($log_stmt = $mysqli->prepare($log_sql)){
        $log_stmt->bind_param("iss", $_SESSION['user_id'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
        $log_stmt->execute();
        $log_stmt->close();
    }
}

// Unset all of the session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Redirect to login page
header("location: login.php");
exit();
?>
