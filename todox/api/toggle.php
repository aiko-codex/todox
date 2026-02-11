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

// First, get current status
$result = mysqli_query($conn, "SELECT is_completed FROM todos WHERE id = $id");

if (!$result || mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo json_encode(array('error' => 'Todo not found'));
    exit;
}

$row = mysqli_fetch_assoc($result);
$current_status = intval($row['is_completed']);
$new_status = $current_status === 1 ? 0 : 1;

// Update the status
$update_result = mysqli_query($conn, "UPDATE todos SET is_completed = $new_status WHERE id = $id");

if ($update_result) {
    // Fetch updated todo
    $result = mysqli_query($conn, "SELECT * FROM todos WHERE id = $id");
    $todo = mysqli_fetch_assoc($result);
    
    // Convert is_completed to boolean
    $todo['is_completed'] = (bool)$todo['is_completed'];
    
    echo json_encode($todo);
} else {
    http_response_code(500);
    echo json_encode(array('error' => 'Failed to update todo'));
}

// Close connection
mysqli_close($conn);
?>