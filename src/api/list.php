```php
<?php
/**
 * List Todos API Endpoint
 * 
 * Fetches all todos from database with optional filtering
 * Supports filters: all, pending, completed
 * Returns JSON response with success status and todos array
 */

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'error' => 'Method not allowed'));
    exit;
}

// Include database connection
require_once __DIR__ . '/../includes/functions.php';

// Get and sanitize filter parameter
$filter = isset($_GET['filter']) ? trim(strtolower($_GET['filter'])) : 'all';

// Validate filter parameter
$validFilters = array('all', 'pending', 'completed');
if (!in_array($filter, $validFilters)) {
    http_response_code(400);
    echo json_encode(array('success' => false, 'error' => 'Invalid filter value. Use: all, pending, completed'));
    exit;
}

// Connect to database
$connection = db_connect();
if (!$connection) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Database connection failed'));
    exit;
}

// Build query based on filter
$query = "SELECT id, title, is_completed, created_at, updated_at FROM todos ";
if ($filter === 'pending') {
    $query .= "WHERE is_completed = 0 ";
} elseif ($filter === 'completed') {
    $query .= "WHERE is_completed = 1 ";
}
$query .= "ORDER BY created_at DESC";

// Execute query
$result = mysqli_query($connection, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Database query failed'));
    mysqli_close($connection);
    exit;
}

// Fetch all todos
$todos = array();
while ($row = mysqli_fetch_assoc($result)) {
    // Convert is_completed to boolean for clearer JSON response
    $row['is_completed'] = (bool)$row['is_completed'];
    $todos[] = $row;
}

// Close database connection
mysqli_free_result($result);
mysqli_close($connection);

// Return successful response
http_response_code(200);
echo json_encode(array(
    'success' => true,
    'todos' => $todos
));
?>
```