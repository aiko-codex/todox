```php
<?php
/**
 * Shared functions for Todox application
 */

/**
 * Connect to MySQL database
 * 
 * @return mysqli|false Database connection object or false on failure
 */
function db_connect() {
    // Database configuration
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'todox';
    
    // Create connection
    $connection = mysqli_connect($host, $username, $password, $database);
    
    // Check connection
    if (!$connection) {
        return false;
    }
    
    return $connection;
}

/**
 * Initialize database tables
 * Creates todos table if it doesn't exist
 * 
 * @param mysqli $connection Database connection object
 * @return bool True on success, false on failure
 */
function init_database($connection) {
    $query = "CREATE TABLE IF NOT EXISTS todos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        is_completed TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    return mysqli_query($connection, $query);
}

/**
 * Get all todos from database with optional filter
 * 
 * @param mysqli $connection Database connection object
 * @param string $filter Filter type (all, pending, completed)
 * @return array|false Array of todos or false on failure
 */
function get_todos($connection, $filter = 'all') {
    $query = "SELECT id, title, is_completed, created_at, updated_at FROM todos ";
    
    if ($filter === 'pending') {
        $query .= "WHERE is_completed = 0 ";
    } elseif ($filter === 'completed') {
        $query .= "WHERE is_completed = 1 ";
    }
    
    $query .= "ORDER BY created_at DESC";
    
    $result = mysqli_query($connection, $query);
    
    if (!$result) {
        return false;
    }
    
    $todos = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $todos[] = $row;
    }
    
    mysqli_free_result($result);
    return $todos;
}

/**
 * Add a new todo
 * 
 * @param mysqli $connection Database connection object
 * @param string $title Todo title
 * @return int|false Inserted ID or false on failure
 */
function add_todo($connection, $title) {
    $stmt = mysqli_prepare($connection, "INSERT INTO todos (title) VALUES (?)");
    
    if (!$stmt) {
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "s", $title);
    
    if (mysqli_stmt_execute($stmt)) {
        $id = mysqli_insert_id($connection);
        mysqli_stmt_close($stmt);
        return $id;
    }
    
    mysqli_stmt_close($stmt);
    return false;
}

/**
 * Update todo completion status
 * 
 * @param mysqli $connection Database connection object
 * @param int $id Todo ID
 * @param bool $is_completed Completion status
 * @return bool True on success, false on failure
 */
function update_todo_status($connection, $id, $is_completed) {
    $stmt = mysqli_prepare($connection, "UPDATE todos SET is_completed = ? WHERE id = ?");
    
    if (!$stmt) {
        return false;
    }
    
    $completed = $is_completed ? 1 : 0;
    mysqli_stmt_bind_param($stmt, "ii", $completed, $id);
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * Delete a todo
 * 
 * @param mysqli $connection Database connection object
 * @param int $id Todo ID
 * @return bool True on success, false on failure
 */
function delete_todo($connection, $id) {
    $stmt = mysqli_prepare($connection, "DELETE FROM todos WHERE id = ?");
    
    if (!$stmt) {
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}
?>
```