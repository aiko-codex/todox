<?php
header('Content-Type: application/json');
require_once 'connect.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Parse and validate JSON input
$data = parseJsonInput();

// Validate title
$title = validateTitle(isset($data['title']) ? $data['title'] : '');

// Connect to database
$connection = getDbConnection();

// Prepare statement to prevent SQL injection
$stmt = mysqli_prepare($connection, "INSERT INTO todos (title, is_completed, created_at, updated_at) VALUES (?, 0, NOW(), NOW())");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement']);
    closeDbConnection($connection);
    exit;
}

// Bind parameters and execute
mysqli_stmt_bind_param($stmt, "s", $title);

if (mysqli_stmt_execute($stmt)) {
    $todo_id = mysqli_insert_id($connection);
    
    // Return the newly created todo
    $response = [
        'id' => $todo_id,
        'title' => $title,
        'is_completed' => false,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create todo']);
}

// Clean up
mysqli_stmt_close($stmt);
closeDbConnection($connection);
?>