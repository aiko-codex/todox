```javascript
/**
 * Todox App - Client-side JavaScript Logic
 * Handles DOM events without page reloads using AJAX
 */
(function() {
    'use strict';

    // DOM Elements
    const todoInput = document.getElementById('todo-input');
    const addBtn = document.getElementById('add-todo');
    const todoList = document.getElementById('todo-list');
    const filterButtons = document.querySelectorAll('[data-filter]');
    const errorMessage = document.getElementById('error-message');

    // Current filter state
    let currentFilter = 'all';

    /**
     * Initialize the application
     */
    function initApp() {
        // Load initial todos
        fetchTodos();

        // Event listeners
        addBtn.addEventListener('click', handleAddTodo);
        todoInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleAddTodo();
            }
        });

        // Filter button handlers
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                filterTodos(filter);
            });
        });
    }

    /**
     * Fetch todos from the API
     * @param {string} filter - Filter type (all|pending|completed)
     */
    function fetchTodos(filter = 'all') {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'src/api/list.php?filter=' + filter, true);
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.todos) {
                        renderTodos(response.todos);
                    } else {
                        showError('Invalid response from server');
                    }
                } catch (e) {
                    showError('Failed to parse server response');
                }
            } else {
                showError('Failed to load todos');
            }
        };
        
        xhr.onerror = function() {
            showError('Network error occurred');
        };
        
        xhr.send();
    }

    /**
     * Add a new todo
     */
    function handleAddTodo() {
        const title = todoInput.value.trim();
        
        if (!title) {
            showError('Please enter a todo item');
            return;
        }
        
        hideError();
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'src/api/add.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        todoInput.value = '';
                        fetchTodos(currentFilter);
                    } else {
                        showError(response.error || 'Failed to add todo');
                    }
                } catch (e) {
                    showError('Failed to process server response');
                }
            } else {
                showError('Failed to add todo');
            }
        };
        
        xhr.onerror = function() {
            showError('Network error occurred');
        };
        
        xhr.send(JSON.stringify({title: title}));
    }

    /**
     * Toggle todo completion status
     * @param {number} id - Todo ID
     */
    function toggleTodo(id) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'src/api/toggle.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        fetchTodos(currentFilter);
                    } else {
                        showError(response.error || 'Failed to update todo');
                    }
                } catch (e) {
                    showError('Failed to process server response');
                }
            } else {
                showError('Failed to update todo');
            }
        };
        
        xhr.onerror = function() {
            showError('Network error occurred');
        };
        
        xhr.send(JSON.stringify({id: id}));
    }

    /**
     * Delete a todo after confirmation
     * @param {number} id - Todo ID
     */
    function deleteTodo(id) {
        if (!confirm('Are you sure you want to delete this todo?')) {
            return;
        }
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'src/api/delete.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        fetchTodos(currentFilter);
                    } else {
                        showError(response.error || 'Failed to delete todo');
                    }
                } catch (e) {
                    showError('Failed to process server response');
                }
            } else {
                showError('Failed to delete todo');
            }
        };
        
        xhr.onerror = function() {
            showError('Network error occurred');
        };
        
        xhr.send(JSON.stringify({id: id}));
    }

    /**
     * Filter todos by status
     * @param {string} filter - Filter type (all|pending|completed)
     */
    function filterTodos(filter) {
        currentFilter = filter;
        setFilterActive(filter);
        fetchTodos(filter);
    }

    /**
     * Render todos in the list
     * @param {Array} todos - Array of todo objects
     */
    function renderTodos(todos) {
        if (!todos.length) {
            todoList.innerHTML = '<div class="col-12 text-center py-3"><p class="text-muted">No todos found</p></div>';
            return;
        }
        
        let html = '';
        todos.forEach(todo => {
            const completedClass = todo.is_completed == 1 ? 'completed' : '';
            const checkedAttr = todo.is_completed == 1 ? 'checked' : '';
            
            html += `
                <div class="col-12 mb-2 todo-item ${completedClass}" data-id="${todo.id}">
                    <div class="card">
                        <div class="card-body d-flex align-items-center">
                            <input type="checkbox" class="mr-3 toggle-checkbox" 
                                   ${checkedAttr} data-id="${todo.id}">
                            <span class="flex-grow-1 todo-title">${escapeHtml(todo.title)}</span>
                            <button class="btn btn-sm btn-danger delete-btn" 
                                    data-id="${todo.id}">Delete</button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        todoList.innerHTML = html;
        
        // Attach event listeners to new elements
        document.querySelectorAll('.toggle-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const id = parseInt(this.getAttribute('data-id'));
                toggleTodo(id);
            });
        });
        
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                deleteTodo(id);
            });
        });
    }

    /**
     * Show error message
     * @param {string} message - Error message to display
     */
    function showError(message) {
        if (errorMessage) {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
            
            // Auto-hide after 5 seconds
            setTimeout(hideError, 5000);
        }
    }

    /**
     * Hide error message
     */
    function hideError() {
        if (errorMessage) {
            errorMessage.style.display = 'none';
        }
    }

    /**
     * Set active filter button
     * @param {string} filter - Active filter
     */
    function setFilterActive(filter) {
        filterButtons.forEach(button => {
            if (button.getAttribute('data-filter') === filter) {
                button.classList.add('active');
            } else {
                button.classList.remove('active');
            }
        });
    }

    /**
     * Escape HTML to prevent XSS
     * @param {string} text - Text to escape
     * @return {string} Escaped text
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize app when DOM is loaded
    document.addEventListener('DOMContentLoaded', initApp);
})();
```