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

// CSRF Protection functions
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_token_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

// Enhanced sanitization
function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Rate limiting for login attempts
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
?>
