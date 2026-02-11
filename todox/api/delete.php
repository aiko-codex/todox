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

// Validate ID
$id = validateId(isset($data['id']) ? $data['id'] : 0);

// Connect to database
$connection = getDbConnection();

// Prepare statement to prevent SQL injection
$stmt = mysqli_prepare($connection, "DELETE FROM todos WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement']);
    closeDbConnection($connection);
    exit;
}

// Bind parameters and execute
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    if ($affected_rows > 0) {
        echo json_encode(['message' => 'Todo deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Todo not found']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete todo']);
}

// Clean up
mysqli_stmt_close($stmt);
closeDbConnection($connection);
?>