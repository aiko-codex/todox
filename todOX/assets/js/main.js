```javascript
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const todoForm = document.getElementById('todo-form');
    const todoInput = document.getElementById('todo-title');
    const todoList = document.getElementById('todo-list');
    const emptyState = document.getElementById('empty-state');
    const filterButtons = document.querySelectorAll('.btn-group .btn');
    
    // Current filter state
    let currentFilter = 'all';
    
    // Load todos on page load
    loadTodos();
    
    // Form submission handler
    todoForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const title = todoInput.value.trim();
        if (title) {
            addTodo(title);
        }
    });
    
    // Filter button handlers
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Update filter and reload list
            currentFilter = this.dataset.filter;
            renderTodos();
        });
    });
    
    // Function to add a new todo
    function addTodo(title) {
        fetch('api/add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'title=' + encodeURIComponent(title)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                todoInput.value = '';
                loadTodos(); // Reload the list
            } else {
                alert('Error adding todo: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding the todo.');
        });
    }
    
    // Function to toggle todo completion status
    function toggleTodo(id) {
        fetch('api/toggle.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + encodeURIComponent(id)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadTodos(); // Reload the list
            } else {
                alert('Error updating todo: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the todo.');
        });
    }
    
    // Function to delete a todo
    function deleteTodo(id, title) {
        if (confirm(`Are you sure you want to delete "${title}"?`)) {
            fetch('api/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + encodeURIComponent(id)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTodos(); // Reload the list
                } else {
                    alert('Error deleting todo: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the todo.');
            });
        }
    }
    
    // Function to load todos from API
    function loadTodos() {
        // Show loading state
        todoList.innerHTML = '<div class="loading"><div class="spinner"></div>Loading...</div>';
        emptyState.style.display = 'none';
        
        fetch('api/list.php')
        .then(response => response.json())
        .then(data => {
            window.allTodos = data.todos || [];
            renderTodos();
        })
        .catch(error => {
            console.error('Error:', error);
            todoList.innerHTML = '<li class="list-group-item text-danger">Error loading todos</li>';
        });
    }
    
    // Function to render todos based on current filter
    function renderTodos() {
        // Filter todos based on current filter
        let filteredTodos = window.allTodos;
        if (currentFilter === 'pending') {
            filteredTodos = window.allTodos.filter(todo => todo.status === 'pending');
        } else if (currentFilter === 'completed') {
            filteredTodos = window.allTodos.filter(todo => todo.status === 'completed');
        }
        
        // Render todos or empty state
        if (filteredTodos.length === 0) {
            todoList.innerHTML = '';
            emptyState.style.display = 'block';
        } else {
            emptyState.style.display = 'none';
            todoList.innerHTML = filteredTodos.map(todo => `
                <li class="todo-item list-group-item ${todo.status}">
                    <input type="checkbox" class="todo-checkbox" ${todo.status === 'completed' ? 'checked' : ''} 
                        onchange="toggleTodo(${todo.id})">
                    <p class="todo-title mb-0">${escapeHtml(todo.title)}</p>
                    <span class="todo-date">${formatDate(todo.created_at)}</span>
                    <button class="todo-delete" onclick="deleteTodo(${todo.id}, '${escapeHtml(todo.title)}')">&times;</button>
                </li>
            `).join('');
        }
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    // Helper function to format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${month}/${day} ${hours}:${minutes}`;
    }
    
    // Make functions available globally for inline event handlers
    window.toggleTodo = toggleTodo;
    window.deleteTodo = deleteTodo;
});
```