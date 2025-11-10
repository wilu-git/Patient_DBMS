<?php
/**
 * Configuration File
 * Contains database credentials, constants, and helper functions
 */

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'patient_dbms');

// Define base paths for the new folder structure
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('VIEWS_PATH', PUBLIC_PATH . '/views');
define('SETUP_PATH', BASE_PATH . '/setup');

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
        // Determine if we're in a subdirectory
        $base = (strpos($_SERVER['PHP_SELF'], '/views/') !== false) ? '../../' : '';
        header("location: " . $base . "login.php");
        exit();
    }
}

function require_role($allowed_roles) {
    require_login();
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        // Determine if we're in a subdirectory
        $base = (strpos($_SERVER['PHP_SELF'], '/views/') !== false) ? '../../' : '';
        header("location: " . $base . "unauthorized.php");
        exit();
    }
}

// Role constants
define('ROLE_DOCTOR', 'doctor');
define('ROLE_SECRETARY', 'secretary');
define('ROLE_DEVELOPER', 'developer');
define('ROLE_ACCOUNTANT', 'accountant');
?>
