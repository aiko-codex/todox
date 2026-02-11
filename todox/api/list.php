<?php
header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('error' => 'Method not allowed'));
    exit;
}

// Get filter parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Include database connection
require_once 'connect.php';
$conn = get_db_connection();

// Build query based on filter
switch ($filter) {
    case 'pending':
        $query = "SELECT * FROM todos WHERE is_completed = 0 ORDER BY created_at DESC";
        break;
    case 'completed':
        $query = "SELECT * FROM todos WHERE is_completed = 1 ORDER BY created_at DESC";
        break;
    default:
        $query = "SELECT * FROM todos ORDER BY created_at DESC";
}

$result = mysqli_query($conn, $query);

if ($result) {
    $todos = array();
    while ($row = mysqli_fetch_assoc($result)) {
        // Convert is_completed to boolean for consistency
        $row['is_completed'] = (bool)$row['is_completed'];
        $todos[] = $row;
    }
    echo json_encode($todos);
} else {
    http_response_code(500);
    echo json_encode(array('error' => 'Failed to fetch todos'));
}

// Close connection
mysqli_close($conn);
?>