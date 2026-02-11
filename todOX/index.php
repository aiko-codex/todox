```php
<?php
// This is the main entry point for the Todox application
// It serves as a static shell that loads data via AJAX
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todox - Simple Todo App</title>
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <!-- Header -->
        <header class="text-center mb-5">
            <h1 class="display-4">Todox</h1>
            <p class="lead">A simple todo application</p>
        </header>

        <!-- Input Bar -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="todo-form">
                    <div class="input-group">
                        <input type="text" class="form-control" id="todo-title" placeholder="What needs to be done?" required>
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">Add</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="d-flex justify-content-center mb-4">
            <div class="btn-group" role="group" aria-label="Todo filters">
                <button type="button" class="btn btn-outline-secondary active" data-filter="all">All</button>
                <button type="button" class="btn btn-outline-secondary" data-filter="pending">Pending</button>
                <button type="button" class="btn btn-outline-secondary" data-filter="completed">Completed</button>
            </div>
        </div>

        <!-- Todo List Container -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Your Tasks</h5>
            </div>
            <div class="card-body">
                <ul id="todo-list" class="list-group">
                    <!-- Todos will be loaded here via JavaScript -->
                </ul>
                
                <!-- Empty State -->
                <p id="empty-state" class="text-center text-muted mt-3">
                    No todos yet. Add one above!
                </p>
            </div>
        </div>

        <!-- Footer -->
        <footer class="text-center mt-5 mb-3">
            <p class="text-muted">© <?php echo date('Y'); ?> Todox. Built with PHP & MySQL.</p>
        </footer>
    </div>

    <!-- Bootstrap 4 JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/main.js"></script>
</body>
</html>
```