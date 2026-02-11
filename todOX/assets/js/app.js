```javascript
// === CONFIG ===
const API_BASE = '/src/api';
const FILTER_ALL = 'all';
const FILTER_PENDING = 'pending';
const FILTER_COMPLETED = 'completed';

// === DOM ELEMENTS ===
const todoForm = document.getElementById('todo-form');
const todoInput = document.getElementById('todo-input');
const todoList = document.getElementById('todo-list');
const filterButtons = document.querySelectorAll('.filter-btn');

// === STATE ===
let todos = []; // in-memory store of todos
let currentFilter = FILTER_ALL; // default filter

// === INIT ===
document.addEventListener('DOMContentLoaded', function() {
    fetchTodos();
    
    // Setup event listeners
    if (todoForm) {
        todoForm.addEventListener('submit', handleAddTodo);
    }
    
    if (filterButtons) {
        filterButtons.forEach(button => {
            button.addEventListener('click', handleFilterChange);
        });
    }
});

// === DATE UTIL ===
function formatDate(isoString) {
    const date = new Date(isoString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}

// === SECURITY UTIL ===
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

// === RENDER UTIL ===
function renderTodos() {
    // Clear current list
    if (!todoList) return;
    todoList.innerHTML = '';

    // Filter todos based on currentFilter
    let filteredTodos = [];
    if (currentFilter === FILTER_ALL) {
        filteredTodos = todos;
    } else if (currentFilter === FILTER_PENDING) {
        filteredTodos = todos.filter(function(todo) {
            return !todo.is_completed;
        });
    } else if (currentFilter === FILTER_COMPLETED) {
        filteredTodos = todos.filter(function(todo) {
            return todo.is_completed;
        });
    }

    // Render each todo
    if (filteredTodos.length === 0) {
        const emptyMessage = document.createElement('li');
        emptyMessage.className = 'no-todos';
        emptyMessage.textContent = 'No todos found';
        todoList.appendChild(emptyMessage);
        return;
    }

    filteredTodos.forEach(function(todo) {
        const li = document.createElement('li');
        li.className = 'todo-item list-group-item d-flex justify-content-between align-items-center';
        if (todo.is_completed) {
            li.classList.add('completed');
        }
        li.dataset.id = todo.id;

        li.innerHTML = `
            <div class="todo-content d-flex align-items-center">
                <input type="checkbox" class="toggle-checkbox mr-2" ${todo.is_completed ? 'checked' : ''}>
                <span class="todo-title ${todo.is_completed ? 'completed-text' : ''}">${escapeHtml(todo.title)}</span>
                <span class="todo-date text-muted small ml-2">(${formatDate(todo.created_at)})</span>
            </div>
            <button class="delete-btn btn btn-sm btn-outline-danger" aria-label="Delete todo">&times;</button>
        `;

        // Attach event listeners
        const toggleCheckbox = li.querySelector('.toggle-checkbox');
        const deleteBtn = li.querySelector('.delete-btn');

        toggleCheckbox.addEventListener('change', function() {
            toggleTodo(todo.id);
        });
        
        deleteBtn.addEventListener('click', function() {
            deleteTodo(todo.id);
        });

        todoList.appendChild(li);
    });
}

// === API UTIL ===
function fetchTodos() {
    fetch(API_BASE + '/list.php')
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(function(data) {
            todos = data;
            renderTodos();
        })
        .catch(function(error) {
            console.error('Failed to fetch todos:', error);
            if (todoList) {
                todoList.innerHTML = '<li class="error list-group-item text-danger">Failed to load todos. Please try again.</li>';
            }
        });
}

function addTodo(title) {
    fetch(API_BASE + '/add.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'title=' + encodeURIComponent(title)
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            fetchTodos(); // Refresh the list
        } else {
            alert('Failed to add todo: ' + data.message);
        }
    })
    .catch(function(error) {
        console.error('Error adding todo:', error);
        alert('Failed to add todo. Please try again.');
    });
}

function toggleTodo(id) {
    fetch(API_BASE + '/toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + encodeURIComponent(id)
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            fetchTodos(); // Refresh the list
        } else {
            alert('Failed to toggle todo: ' + data.message);
        }
    })
    .catch(function(error) {
        console.error('Error toggling todo:', error);
        alert('Failed to toggle todo. Please try again.');
    });
}

function deleteTodo(id) {
    if (!confirm('Are you sure you want to delete this todo?')) {
        return;
    }
    
    fetch(API_BASE + '/delete.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + encodeURIComponent(id)
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            fetchTodos(); // Refresh the list
        } else {
            alert('Failed to delete todo: ' + data.message);
        }
    })
    .catch(function(error) {
        console.error('Error deleting todo:', error);
        alert('Failed to delete todo. Please try again.');
    });
}

// === EVENT HANDLERS ===
function handleAddTodo(e) {
    e.preventDefault();
    
    if (!todoInput) return;
    
    const title = todoInput.value.trim();
    if (title === '') {
        alert('Please enter a todo title');
        return;
    }
    
    addTodo(title);
    todoInput.value = ''; // Clear input
}

function handleFilterChange(e) {
    // Update current filter
    currentFilter = e.target.dataset.filter || FILTER_ALL;
    
    // Update UI - remove active class from all buttons
    filterButtons.forEach(function(button) {
        button.classList.remove('active');
    });
    
    // Add active class to clicked button
    e.target.classList.add('active');
    
    // Re-render todos
    renderTodos();
}
```