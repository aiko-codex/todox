<?php
header('Content-Type: application/json');
require_once 'connect.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get filter parameter if provided
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$valid_filters = ['all', 'pending', 'completed'];

if (!in_array($filter, $valid_filters)) {
    $filter = 'all';
}

// Connect to database
$connection = getDbConnection();

// Build query based on filter
$query = "SELECT id, title, is_completed, created_at, updated_at FROM todos";
$params = [];
$types = "";

switch ($filter) {
    case 'pending':
        $query .= " WHERE is_completed = 0";
        break;
    case 'completed':
        $query .= " WHERE is_completed = 1";
        break;
    default:
        // No additional WHERE clause for 'all'
        break;
}

$query .= " ORDER BY created_at DESC";

// Prepare statement
$stmt = mysqli_prepare($connection, $query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement']);
    closeDbConnection($connection);
    exit;
}

// Bind parameters if needed
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

// Execute and fetch results
if (!mysqli_stmt_execute($stmt)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to execute query']);
    mysqli_stmt_close($stmt);
    closeDbConnection($connection);
    exit;
}

$result = mysqli_stmt_get_result($stmt);

$todos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $todos[] = [
        'id' => intval($row['id']),
        'title' => htmlspecialchars_decode($row['title'], ENT_QUOTES),
        'is_completed' => boolval($row['is_completed']),
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    ];
}

echo json_encode($todos);

// Clean up
mysqli_stmt_close($stmt);
closeDbConnection($connection);
?>