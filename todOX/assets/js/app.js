```javascript
/**
 * Validates the todo title input field.
 * @param {HTMLInputElement} input - The input element for todo title
 * @returns {Object} { isValid: boolean, error: string | null }
 */
function validateTodoInput(input) {
  const value = input.value.trim();
  if (value === '') {
    return {
      isValid: false,
      error: 'Todo title cannot be empty.'
    };
  }
  return {
    isValid: true,
    error: null
  };
}

/**
 * Displays an error state on the todo input field.
 * @param {HTMLInputElement} input - The input element
 * @param {string} message - Error message to display
 */
function showInputError(input, message) {
  input.classList.add('is-invalid');
  
  // Create or update error message element
  let errorMsg = input.parentNode.querySelector('.invalid-feedback');
  if (!errorMsg) {
    errorMsg = document.createElement('div');
    errorMsg.className = 'invalid-feedback';
    input.parentNode.appendChild(errorMsg);
  }
  errorMsg.textContent = message;
}

/**
 * Clears the error state from the todo input field.
 * @param {HTMLInputElement} input - The input element
 */
function clearInputError(input) {
  input.classList.remove('is-invalid');
  
  const errorMsg = input.parentNode.querySelector('.invalid-feedback');
  if (errorMsg) {
    errorMsg.remove();
  }
}

/**
 * Adds a new todo item
 */
function addTodo() {
  const input = document.getElementById('todo-title');
  const validation = validateTodoInput(input);

  if (!validation.isValid) {
    showInputError(input, validation.error);
    return; // Prevent submission
  }

  // Clear any previous errors
  clearInputError(input);

  // Proceed with API call
  const title = input.value.trim();

  const xhr = new XMLHttpRequest();
  xhr.open('POST', './api/add.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        try {
          const response = JSON.parse(xhr.responseText);
          if (response.success) {
            input.value = ''; // Clear input
            renderTodos(); // Re-render list
          } else {
            showInputError(input, response.message || 'Failed to add todo');
          }
        } catch (e) {
          showInputError(input, 'Invalid server response');
        }
      } else {
        showInputError(input, 'Server error. Please try again.');
      }
    }
  };
  xhr.send('title=' + encodeURIComponent(title));
}

/**
 * Renders the todo list
 */
function renderTodos(filter) {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', './api/list.php' + (filter ? '?filter=' + filter : ''), true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      document.getElementById('todo-list').innerHTML = xhr.responseText;
    }
  };
  xhr.send();
}

/**
 * Toggles todo completion status
 * @param {number} id - Todo ID
 */
function toggleTodo(id) {
  const xhr = new XMLHttpRequest();
  xhr.open('POST', './api/toggle.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      renderTodos();
    }
  };
  xhr.send('id=' + id);
}

/**
 * Deletes a todo item
 * @param {number} id - Todo ID
 */
function deleteTodo(id) {
  if (confirm('Are you sure you want to delete this todo?')) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', './api/delete.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        renderTodos();
      }
    };
    xhr.send('id=' + id);
  }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  renderTodos();
  
  // Add event listener for Enter key in input
  const input = document.getElementById('todo-title');
  input.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      addTodo();
    }
  });
});
```