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

// Start session for user authentication with security settings
if (session_status() == PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    // Use secure cookies if HTTPS is available
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    
    session_start();
    
    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Session started more than 30 minutes ago
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

/**
 * Sanitize user input to prevent XSS attacks
 * 
 * @param string $data The input data to sanitize
 * @return string The sanitized data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is currently logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Require user to be logged in, redirect to login page if not
 * 
 * @return void
 */
function require_login() {
    if (!is_logged_in()) {
        // Determine if we're in a subdirectory
        $base = (strpos($_SERVER['PHP_SELF'], '/views/') !== false) ? '../../' : '';
        header("location: " . $base . "login.php");
        exit();
    }
}

/**
 * Require user to have specific role(s), redirect to unauthorized page if not
 * 
 * @param array $allowed_roles Array of allowed role strings
 * @return void
 */
function require_role($allowed_roles) {
    require_login();
    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        // Determine if we're in a subdirectory
        $base = (strpos($_SERVER['PHP_SELF'], '/views/') !== false) ? '../../' : '';
        header("location: " . $base . "unauthorized.php");
        exit();
    }
}

/**
 * Generate a CSRF token for form protection
 * 
 * @return string The CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from form submission
 * 
 * @param string $token The token to verify
 * @return bool True if token is valid, false otherwise
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate hidden CSRF token field for forms
 * 
 * @return string HTML input field with CSRF token
 */
function csrf_token_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Enhanced output sanitization to prevent XSS
 * 
 * @param string $data The data to sanitize
 * @return string The sanitized data safe for HTML output
 */
function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user has exceeded login attempt limit
 * 
 * @param string $username The username attempting to login
 * @return bool True if login attempts are within limit, false otherwise
 */
function check_login_attempts($username) {
    global $mysqli;
    
    // Check failed login attempts in the last 15 minutes
    $sql = "SELECT COUNT(*) as attempts FROM audit_log 
            WHERE action = 'FAILED_LOGIN' 
            AND table_name = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        // Allow max 5 attempts in 15 minutes
        return $row['attempts'] < 5;
    }
    
    return true; // If query fails, allow login attempt
}

/**
 * Log a failed login attempt
 * 
 * @param string $username The username that failed to login
 * @return void
 */
function log_failed_login($username) {
    global $mysqli;
    
    $sql = "INSERT INTO audit_log (user_id, action, table_name, ip_address, user_agent) 
            VALUES (NULL, 'FAILED_LOGIN', ?, ?, ?)";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("sss", $username, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
        $stmt->execute();
        $stmt->close();
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
