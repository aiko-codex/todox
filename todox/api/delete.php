<?php
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method not allowed'));
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['id'])) {
    http_response_code(400);
    echo json_encode(array('error' => 'Todo ID is required'));
    exit;
}

$id = intval($input['id']);

// Include database connection
require_once 'connect.php';
$conn = get_db_connection();

// Check if todo exists
$result = mysqli_query($conn, "SELECT id FROM todos WHERE id = $id");

if (!$result || mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo json_encode(array('error' => 'Todo not found'));
    exit;
}

// Delete the todo
$delete_result = mysqli_query($conn, "DELETE FROM todos WHERE id = $id");

if ($delete_result) {
    echo json_encode(array('success' => true));
} else {
    http_response_code(500);
    echo json_encode(array('error' => 'Failed to delete todo'));
}

// Close connection
mysqli_close($conn);
?>