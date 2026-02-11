```php
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'todox');

/**
 * Create database connection
 * @return mysqli Connection object
 */
function get_db_connection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if (!$conn) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'Database connection failed'));
        exit;
    }
    
    // Set charset to UTF-8
    mysqli_set_charset($conn, 'utf8');
    
    // Create todos table if it doesn't exist
    $create_table_query = "
        CREATE TABLE IF NOT EXISTS todos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            is_completed TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
    if (!mysqli_query($conn, $create_table_query)) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'Failed to create table'));
        exit;
    }
    
    return $conn;
}
?>