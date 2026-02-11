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

// First, get current status
$stmt = mysqli_prepare($connection, "SELECT is_completed FROM todos WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement']);
    closeDbConnection($connection);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Todo not found']);
    mysqli_stmt_close($stmt);
    closeDbConnection($connection);
    exit;
}

$row = mysqli_fetch_assoc($result);
$current_status = intval($row['is_completed']);
$new_status = $current_status === 1 ? 0 : 1;

mysqli_stmt_close($stmt);

// Update the status
$stmt = mysqli_prepare($connection, "UPDATE todos SET is_completed = ?, updated_at = NOW() WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare update statement']);
    closeDbConnection($connection);
    exit;
}

mysqli_stmt_bind_param($stmt, "ii", $new_status, $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'id' => $id,
        'is_completed' => $new_status === 1
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update todo']);
}

// Clean up
mysqli_stmt_close($stmt);
closeDbConnection($connection);
?>