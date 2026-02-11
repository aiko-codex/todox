```javascript
// Global variables
let currentFilter = 'all';

// Initialize the app
document.addEventListener('DOMContentLoaded', function() {
    loadTodos();
    
    // Handle form submission
    document.getElementById('todo-form').addEventListener('submit', function(e) {
        e.preventDefault();
        addTodo();
    });
    
    // Handle filter clicks
    document.querySelectorAll('.filter-btn').forEach(button => {
        button.addEventListener('click', function() {
            setActiveFilter(this.dataset.filter);
        });
    });
});

// Load todos from API
async function loadTodos() {
    try {
        const response = await fetch('/todox/api/list.php');
        const todos = await response.json();
        renderTodos(todos);
    } catch (error) {
        console.error('Error loading todos:', error);
    }
}

// Add new todo
async function addTodo() {
    const input = document.getElementById('todo-input');
    const title = input.value.trim();
    
    if (!title) {
        alert('Please enter a todo item');
        return;
    }
    
    try {
        const response = await fetch('/todox/api/add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ title: title })
        });
        
        if (response.ok) {
            input.value = '';
            loadTodos(); // Reload the list
        } else {
            alert('Failed to add todo');
        }
    } catch (error) {
        console.error('Error adding todo:', error);
        alert('Failed to add todo');
    }
}

// Toggle todo completion status
async function toggleTodo(id, completed) {
    try {
        const response = await fetch('/todox/api/toggle.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id, completed: !completed })
        });
        
        if (response.ok) {
            loadTodos(); // Reload the list
        } else {
            alert('Failed to update todo');
        }
    } catch (error) {
        console.error('Error updating todo:', error);
        alert('Failed to update todo');
    }
}

// Delete todo with confirmation and animation
function deleteTodo(id) {
    const todoElement = document.querySelector(`[data-id="${id}"]`);
    if (!todoElement) return;

    // Show native confirmation dialog
    const confirmed = window.confirm("Are you sure you want to delete this task? This cannot be undone.");
    if (!confirmed) return;

    // Fade out animation
    todoElement.classList.add('fade-out');

    // After fade-out completes, send AJAX request and remove from DOM
    setTimeout(async () => {
        try {
            const response = await fetch('/todox/api/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Remove element from DOM permanently after successful deletion
            todoElement.remove();

            // Optional: Show user feedback
            console.log(`Todo with ID ${id} deleted successfully.`);
        } catch (error) {
            console.error('Failed to delete todo:', error);
            // Revert fade-out on error
            todoElement.classList.remove('fade-out');
            // Show error message
            alert('Failed to delete task. Please try again.');
        }
    }, 300); // Match CSS transition duration
}

// Set active filter
function setActiveFilter(filter) {
    currentFilter = filter;
    
    // Update UI
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-filter="${filter}"]`).classList.add('active');
    
    // Reload todos with filter
    loadTodos();
}

// Render todos to the page
function renderTodos(todos) {
    const todoList = document.getElementById('todo-list');
    todoList.innerHTML = '';
    
    // Filter todos based on current filter
    let filteredTodos = todos;
    if (currentFilter === 'active') {
        filteredTodos = todos.filter(todo => !todo.completed);
    } else if (currentFilter === 'completed') {
        filteredTodos = todos.filter(todo => todo.completed);
    }
    
    if (filteredTodos.length === 0) {
        todoList.innerHTML = '<div class="text-center text-muted py-4">No todos found</div>';
        return;
    }
    
    // Create todo elements
    filteredTodos.forEach(todo => {
        const todoElement = document.createElement('div');
        todoElement.className = 'todo-item card mb-2';
        todoElement.dataset.id = todo.id;
        
        // Add completed class if needed
        if (todo.completed) {
            todoElement.classList.add('completed');
        }
        
        todoElement.innerHTML = `
            <div class="card-body d-flex align-items-center">
                <input type="checkbox" class="mr-3 toggle-checkbox" ${todo.completed ? 'checked' : ''}>
                <span class="flex-grow-1">${todo.title}</span>
                <button class="btn btn-sm btn-danger delete-btn">Delete</button>
            </div>
        `;
        
        todoList.appendChild(todoElement);
    });
    
    // Add event listeners for toggle checkboxes
    document.querySelectorAll('.toggle-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const todoItem = this.closest('.todo-item');
            const id = todoItem.dataset.id;
            const completed = this.checked;
            toggleTodo(id, completed);
        });
    });
    
    // Add event listeners for delete buttons using event delegation
    // This replaces the previous direct attachment to ensure dynamically added buttons work
}

// Use event delegation for delete buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('delete-btn')) {
        e.preventDefault();
        const todoItem = e.target.closest('[data-id]');
        if (!todoItem) return;
        const id = todoItem.dataset.id;
        deleteTodo(id);
    }
});
```