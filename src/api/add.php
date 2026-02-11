<?php
/**
 * Add Todo API Endpoint
 * 
 * Accepts JSON payload with todo title, validates it,
 * inserts into database, and returns created todo.
 */

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check content type
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if ($contentType !== 'application/json') {
    http_response_code(400);
    echo json_encode(['error' => 'Content-Type must be application/json']);
    exit;
}

// Get JSON input
$jsonInput = file_get_contents('php://input');
$inputData = json_decode($jsonInput, true);

// Validate JSON
if (!is_array($inputData)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON format']);
    exit;
}

// Validate title field
if (!isset($inputData['title']) || !is_string($inputData['title'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Title is required and must be a string']);
    exit;
}

$title = trim($inputData['title']);

// Check if title is not empty
if (empty($title)) {
    http_response_code(400);
    echo json_encode(['error' => 'Title cannot be empty']);
    exit;
}

// Limit title length to prevent database issues
if (strlen($title) > 512) {
    http_response_code(400);
    echo json_encode(['error' => 'Title is too long (max 512 characters)']);
    exit;
}

// Include database functions
require_once '../includes/functions.php';

// Try to add the todo
$result = addTodo($title);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create todo']);
    exit;
}

// Return success response with created todo
http_response_code(201);
echo json_encode($result);
exit;
?>