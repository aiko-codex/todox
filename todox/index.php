<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todox — Simple Todo List</title>
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 700px;
            margin-top: 50px;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .todo-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .todo-item.completed .todo-title {
            text-decoration: line-through;
            color: #6c757d;
        }
        .todo-title {
            flex-grow: 1;
            margin-left: 15px;
            word-break: break-word;
        }
        .filters {
            margin: 20px 0;
        }
        .todo-form {
            margin-bottom: 30px;
        }
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #6c757d;
        }
        .loading {
            text-align: center;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Todox</h1>

        <form id="todo-form" class="todo-form">
            <div class="input-group">
                <input type="text" id="todo-input" name="title" class="form-control" placeholder="What needs to be done?" autocomplete="off" required>
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </div>
        </form>

        <div class="filters text-center">
            <div class="btn-group" role="group">
                <button class="filter-btn btn btn-outline-secondary active" data-filter="all">All</button>
                <button class="filter-btn btn btn-outline-secondary" data-filter="pending">Pending</button>
                <button class="filter-btn btn btn-outline-secondary" data-filter="completed">Completed</button>
            </div>
        </div>

        <div id="todo-list-container">
            <div class="loading">Loading todos...</div>
        </div>
    </div>

    <!-- Bootstrap 4 JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        $(document).ready(function() {
            let currentFilter = 'all';
            
            // Load todos on page load
            loadTodos();
            
            // Handle form submission
            $('#todo-form').on('submit', function(e) {
                e.preventDefault();
                
                const title = $('#todo-input').val().trim();
                if (!title) return;
                
                $.ajax({
                    url: 'api/add.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ title: title }),
                    success: function(todo) {
                        $('#todo-input').val('');
                        loadTodos(); // Reload the list
                    },
                    error: function(xhr) {
                        alert('Error adding todo: ' + xhr.responseJSON.error);
                    }
                });
            });
            
            // Handle filter buttons
            $('.filter-btn').on('click', function() {
                $('.filter-btn').removeClass('active btn-secondary').addClass('btn-outline-secondary');
                $(this).removeClass('btn-outline-secondary').addClass('active btn-secondary');
                currentFilter = $(this).data('filter');
                loadTodos();
            });
            
            // Handle todo toggle
            $(document).on('click', '.toggle-todo', function() {
                const id = $(this).data('id');
                
                $.ajax({
                    url: 'api/toggle.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ id: id }),
                    success: function(todo) {
                        loadTodos(); // Reload the list
                    },
                    error: function(xhr) {
                        alert('Error updating todo: ' + xhr.responseJSON.error);
                    }
                });
            });
            
            // Handle todo deletion
            $(document).on('click', '.delete-todo', function() {
                const id = $(this).data('id');
                const title = $(this).closest('.todo-item').find('.todo-title').text();
                
                if (confirm(`Are you sure you want to delete "${title}"?`)) {
                    $.ajax({
                        url: 'api/delete.php',
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({ id: id }),
                        success: function(response) {
                            loadTodos(); // Reload the list
                        },
                        error: function(xhr) {
                            alert('Error deleting todo: ' + xhr.responseJSON.error);
                        }
                    });
                }
            });
            
            // Function to load todos
            function loadTodos() {
                $('#todo-list-container').html('<div class="loading">Loading todos...</div>');
                
                $.ajax({
                    url: 'api/list.php?filter=' + currentFilter,
                    method: 'GET',
                    success: function(todos) {
                        renderTodos(todos);
                    },
                    error: function(xhr) {
                        $('#todo-list-container').html('<div class="alert alert-danger">Error loading todos: ' + xhr.responseJSON.error + '</div>');
                    }
                });
            }
            
            // Function to render todos
            function renderTodos(todos) {
                if (todos.length === 0) {
                    $('#todo-list-container').html(`
                        <div class="empty-state">
                            <p>No todos found.</p>
                            <small>Add a new todo above to get started!</small>
                        </div>
                    `);
                    return;
                }
                
                let html = '<ul class="list-unstyled" id="todo-list">';
                
                todos.forEach(function(todo) {
                    const completedClass = todo.is_completed ? 'completed' : '';
                    const checkedAttr = todo.is_completed ? 'checked' : '';
                    
                    html += `
                        <li class="todo-item ${completedClass}" data-id="${todo.id}">
                            <input type="checkbox" class="toggle-todo" data-id="${todo.id}" ${checkedAttr}>
                            <span class="todo-title">${escapeHtml(todo.title)}</span>
                            <button class="btn btn-sm btn-danger delete-todo ml-2" data-id="${todo.id}">Delete</button>
                        </li>
                    `;
                });
                
                html += '</ul>';
                $('#todo-list-container').html(html);
            }
            
            // Helper function to escape HTML
            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }
        });
    </script>
</body>
</html>