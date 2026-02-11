<?php
// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
    exit;
}

// Include database connection
require_once __DIR__ . '/../includes/functions.php';

// Get raw POST body and decode JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input
if (!isset($data['id']) || !is_numeric($data['id']) || intval($data['id']) <= 0) {
    http_response_code(400);
    echo json_encode(array('success' => false, 'message' => 'Invalid or missing todo ID'));
    exit;
}

$todo_id = intval($data['id']);

// Get database connection
$conn = get_db_connection();

// Check connection
if (!$conn) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Database connection failed'));
    exit;
}

// Prepare and execute DELETE query
$stmt = mysqli_prepare($conn, 'DELETE FROM todos WHERE id = ?');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Failed to prepare statement'));
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $todo_id);
$result = mysqli_stmt_execute($stmt);

if (!$result) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Failed to execute delete operation'));
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}

// Check if any row was affected
$affected_rows = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);
mysqli_close($conn);

if ($affected_rows === 0) {
    http_response_code(404);
    echo json_encode(array('success' => false, 'message' => 'Todo not found'));
    exit;
}

// Success response
echo json_encode(array('success' => true, 'message' => 'Todo deleted'));
?>