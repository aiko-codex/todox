<?php
/**
 * Shared Utility Functions for Todox Application
 * 
 * Provides:
 * - Standardized JSON API responses
 * - Input sanitization and validation
 * - CSRF protection for POST requests
 * - Timestamp formatting utilities
 */

// Start session for CSRF token storage
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Send JSON response and terminate execution
 * @param array $payload Data to encode
 * @param int $status_code HTTP status code
 */
function json_response($payload, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

/**
 * Standardized success envelope
 * @param array $data Response payload (todos, id, etc.)
 * @param string $message Human-readable message
 */
function success_response($data = array(), $message = 'Success') {
    $response = array(
        'success' => true,
        'data' => $data,
        'message' => $message
    );
    json_response($response, 200);
}

/**
 * Standardized error envelope
 * @param string $message Error description
 * @param int $status_code HTTP status (400, 401, 404, 500)
 * @param array $errors Detailed validation errors [field => message]
 */
function error_response($message, $status_code = 400, $errors = array()) {
    $response = array(
        'success' => false,
        'error' => $message,
        'code' => $status_code
    );
    
    if (!empty($errors)) {
        $response['details'] = $errors;
    }
    
    json_response($response, $status_code);
}

/**
 * Sanitize user input for storage
 * - Strip HTML/PHP tags (prevents stored XSS)
 * - Trim whitespace
 * - Convert special chars to entities if $strict=true
 * 
 * @param string|null $input Raw input
 * @param bool $strict If true, also applies htmlspecialchars
 * @return string Cleaned string
 */
function sanitize_input($input, $strict = false) {
    if ($input === null) {
        return '';
    }
    
    // Remove HTML/PHP tags
    $clean = strip_tags($input);
    
    // Trim whitespace
    $clean = trim($clean);
    
    // Apply additional HTML entity encoding if strict mode
    if ($strict) {
        $clean = htmlspecialchars($clean, ENT_QUOTES, 'UTF-8');
    }
    
    return $clean;
}

/**
 * Validate required fields exist and are non-empty
 * 
 * @param array $required List of required field names
 * @param array $data Input data (usually $_POST)
 * @return array Empty array if valid, else [field => error_message]
 */
function validate_required($required, $data) {
    $errors = array();
    
    foreach ($required as $field) {
        if (!isset($data[$field]) || !is_valid_string($data[$field])) {
            $errors[$field] = 'This field is required';
        }
    }
    
    return $errors;
}

/**
 * Check if value is non-empty string
 * @param mixed $value
 * @return bool
 */
function is_valid_string($value) {
    return isset($value) && is_string($value) && trim($value) !== '';
}

/**
 * Format timestamps for display
 * @param string $timestamp ISO format timestamp
 * @return string Formatted date string
 */
function format_timestamp($timestamp) {
    if (!$timestamp) {
        return 'Unknown';
    }
    
    $date = new DateTime($timestamp);
    return $date->format('M j, Y g:i A');
}

/**
 * Initialize CSRF token in session
 * Call this at application start (index.php) or before forms
 */
function init_csrf() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

/**
 * Generate or retrieve current CSRF token
 * @return string 32-byte hex token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate token from request against session
 * @param string|null $token Usually $_POST['csrf_token'] or $_SERVER['HTTP_X_CSRF_TOKEN']
 * @param bool $regenerate If true, rotate token after validation (one-time use)
 * @return bool True if valid
 */
function verify_csrf_token($token, $regenerate = false) {
    // Check if we have a token in session
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Check if token was provided
    if (!$token) {
        return false;
    }
    
    // Verify token matches
    $valid = hash_equals($_SESSION['csrf_token'], $token);
    
    // Regenerate if requested (for one-time use tokens)
    if ($valid && $regenerate) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $valid;
}

/**
 * Get HTML hidden input field with current token
 * @return string <input type="hidden" ...>
 */
function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Get CSRF token for use in AJAX headers
 * @return string Token value
 */
function get_csrf_header() {
    return generate_csrf_token();
}
?>