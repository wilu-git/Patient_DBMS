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
if($mysqli->connect_error){
    // Log error for debugging (in production, log to file instead of displaying)
    error_log("Database Connection Failed: " . $mysqli->connect_error);
    die("ERROR: Could not connect to database. Please contact the system administrator.");
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

// Database helper functions

/**
 * Execute a prepared statement with error handling
 * @param mysqli $mysqli Database connection
 * @param string $sql SQL query with placeholders
 * @param string $types Parameter types (e.g., "ssi")
 * @param array $params Array of parameters
 * @return mysqli_stmt|false Statement object or false on failure
 */
function execute_query($mysqli, $sql, $types = "", $params = []) {
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        error_log("SQL Prepare Error: " . $mysqli->error);
        return false;
    }
    
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("SQL Execute Error: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    return $stmt;
}

/**
 * Log user actions to audit log
 * @param mysqli $mysqli Database connection
 * @param int $user_id User ID
 * @param string $action Action performed
 * @param string $table_name Table name
 * @param int $record_id Record ID
 * @param mixed $old_values Old values (optional)
 * @param mixed $new_values New values (optional)
 */
function log_audit($mysqli, $user_id, $action, $table_name, $record_id = null, $old_values = null, $new_values = null) {
    $sql = "INSERT INTO audit_log (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $old_json = $old_values ? json_encode($old_values) : null;
        $new_json = $new_values ? json_encode($new_values) : null;
        
        $stmt->bind_param("ississss", $user_id, $action, $table_name, $record_id, $old_json, $new_json, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Generate unique ID with prefix
 * @param string $prefix Prefix for the ID (e.g., "P" for patient)
 * @param int $length Length of numeric part
 * @return string Generated ID
 */
function generate_unique_id($prefix, $length = 5) {
    return $prefix . str_pad(mt_rand(1, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Validate date format and ensure it's not in the future (for birthdates)
 * @param string $date Date string
 * @param bool $allow_future Whether to allow future dates
 * @return bool True if valid
 */
function validate_date($date, $allow_future = false) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        return false;
    }
    
    if (!$allow_future && $d > new DateTime()) {
        return false;
    }
    
    return true;
}

/**
 * Format currency amount
 * @param float $amount Amount to format
 * @return string Formatted amount
 */
function format_currency($amount) {
    return '$' . number_format($amount, 2);
}
?>
