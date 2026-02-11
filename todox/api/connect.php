<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(__FILE__)) . '/');
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'todox_user');
define('DB_PASS', 'todox_pass');
define('DB_NAME', 'todox_db');

/**
 * Creates a secure database connection
 * @return mysqli
 */
function getDbConnection() {
    $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$connection) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }
    
    // Set charset to prevent SQL injection
    mysqli_set_charset($connection, 'utf8mb4');
    
    return $connection;
}

/**
 * Validates and decodes JSON input
 * @return array|null
 */
function parseJsonInput() {
    $input = file_get_contents('php://input');
    
    if ($input === false) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to read request body']);
        exit;
    }
    
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid JSON: ' . json_last_error_msg()]);
        exit;
    }
    
    if (!is_array($data)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Request body must be a JSON object']);
        exit;
    }
    
    return $data;
}

/**
 * Sanitizes and validates todo title
 * @param string $title
 * @return string
 */
function validateTitle($title) {
    if (!is_string($title)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Title must be a string']);
        exit;
    }
    
    $title = trim($title);
    
    if (empty($title)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Title cannot be empty']);
        exit;
    }
    
    if (strlen($title) > 500) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Title exceeds 500 characters']);
        exit;
    }
    
    // Additional sanitization
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    
    return $title;
}

/**
 * Validates integer ID
 * @param mixed $id
 * @return int
 */
function validateId($id) {
    if (!is_numeric($id) || intval($id) <= 0) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid ID provided']);
        exit;
    }
    
    return intval($id);
}

/**
 * Closes database connection safely
 * @param mysqli $connection
 */
function closeDbConnection($connection) {
    if ($connection) {
        mysqli_close($connection);
    }
}
?>