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
if (!isset($input['title']) || empty(trim($input['title']))) {
    http_response_code(400);
    echo json_encode(array('error' => 'Title is required'));
    exit;
}

$title = trim($input['title']);

// Limit title length
if (strlen($title) > 255) {
    http_response_code(400);
    echo json_encode(array('error' => 'Title too long'));
    exit;
}

// Include database connection
require_once 'connect.php';
$conn = get_db_connection();

// Prepare statement to prevent SQL injection
$stmt = mysqli_prepare($conn, "INSERT INTO todos (title) VALUES (?)");
mysqli_stmt_bind_param($stmt, "s", $title);

if (mysqli_stmt_execute($stmt)) {
    // Get the inserted ID
    $inserted_id = mysqli_insert_id($conn);
    
    // Fetch the newly inserted todo
    $result = mysqli_query($conn, "SELECT * FROM todos WHERE id = $inserted_id");
    $todo = mysqli_fetch_assoc($result);
    
    http_response_code(201);
    echo json_encode($todo);
} else {
    http_response_code(500);
    echo json_encode(array('error' => 'Failed to add todo'));
}

// Close connections
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>