<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'patient_dbms');

/* Attempt to connect to MySQL database */
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($mysqli === false){
    die("ERROR: Could not connect. " . $mysqli->connect_error);
}

// Set charset to utf8
$mysqli->set_charset("utf8");

// Start session for user authentication
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function require_login() {
    if (!is_logged_in()) {
        header("location: login.php");
        exit();
    }
}

function require_role($allowed_roles) {
    require_login();
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        header("location: unauthorized.php");
        exit();
    }
}

// Role constants
define('ROLE_DOCTOR', 'doctor');
define('ROLE_SECRETARY', 'secretary');
define('ROLE_DEVELOPER', 'developer');
define('ROLE_ACCOUNTANT', 'accountant');
?>
