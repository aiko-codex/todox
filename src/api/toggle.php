<?php
/**
 * Toggle Todo Completion Status
 * 
 * This endpoint accepts a todo ID via POST, flips its completion status,
 * updates the timestamp, and returns the updated record as JSON.
 */

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

// Include database connection
require_once __DIR__ . '/connect.php';

// Get input data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// If JSON decode failed, try to get from POST data
if (empty($data)) {
    $data = $_POST;
}

// Validate input
if (!isset($data['id']) || !is_numeric($data['id']) || $data['id'] <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid or missing todo ID.']);
    exit;
}

$todo_id = (int)$data['id'];

// Begin transaction for atomic operation
mysqli_autocommit($conn, false);

try {
    // Update the todo item - toggle is_completed flag
    $update_query = "UPDATE todos SET is_completed = 1 - is_completed, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare update statement');
    }
    
    mysqli_stmt_bind_param($stmt, "i", $todo_id);
    $result = mysqli_stmt_execute($stmt);
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    // Check if a row was actually updated
    if ($affected_rows === 0) {
        throw new Exception('Todo not found');
    }
    
    // Fetch the updated todo
    $select_query = "SELECT id, title, is_completed, created_at, updated_at FROM todos WHERE id = ?";
    $stmt = mysqli_prepare($conn, $select_query);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare select statement');
    }
    
    mysqli_stmt_bind_param($stmt, "i", $todo_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $todo = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Return success response with updated todo
    echo json_encode([
        'success' => true,
        'todo' => $todo
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
} finally {
    // Restore autocommit mode
    mysqli_autocommit($conn, true);
    
    // Close database connection
    mysqli_close($conn);
}
?>