```javascript
// Utility function to escape HTML
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

// Load todos via AJAX
function loadTodos(filter) {
  // Set default filter if not provided
  if (typeof filter === 'undefined') {
    filter = 'all';
  }
  
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'api/list.php?filter=' + encodeURIComponent(filter), true);
  
  xhr.onload = function() {
    if (xhr.status === 200) {
      var todos = JSON.parse(xhr.responseText);
      var todoList = document.getElementById('todo-list');
      
      // Clear current list
      todoList.innerHTML = '';
      
      if (todos.length === 0) {
        todoList.innerHTML = '<li class="list-group-item text-center">No todos found.</li>';
        return;
      }
      
      // Add each todo to the list
      todos.forEach(function(todo) {
        var li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        if (todo.is_completed) {
          li.classList.add('completed');
        }
        li.dataset.id = todo.id;
        
        li.innerHTML = 
          '<div>' +
            '<input type="checkbox" class="toggle-checkbox mr-2" ' + (todo.is_completed ? 'checked' : '') + '>' +
            '<span class="todo-title">' + escapeHtml(todo.title) + '</span>' +
          '</div>' +
          '<div>' +
            '<small class="text-muted mr-2">' + todo.created_at + '</small>' +
            '<button class="btn btn-sm btn-danger delete-btn">&times;</button>' +
          '</div>';
        
        todoList.appendChild(li);
      });
      
      // Re-attach event listeners
      attachEventListeners();
    } else {
      console.error('Failed to load todos');
    }
  };
  
  xhr.send();
}

// Add a new todo
function addTodo(title) {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'api/add.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  
  xhr.onload = function() {
    if (xhr.status === 200) {
      document.getElementById('todo-input').value = '';
      // Reload todos with current filter
      var activeTab = document.querySelector('.filter-tab.active');
      var filter = activeTab ? activeTab.dataset.filter : 'all';
      loadTodos(filter);
    } else {
      console.error('Failed to add todo');
    }
  };
  
  xhr.send('title=' + encodeURIComponent(title));
}

// Toggle todo completion status
function toggleTodo(id, isCompleted) {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'api/toggle.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  
  xhr.onload = function() {
    if (xhr.status !== 200) {
      console.error('Failed to toggle todo');
    }
  };
  
  xhr.send('id=' + encodeURIComponent(id) + '&is_completed=' + (isCompleted ? '0' : '1'));
}

// Delete a todo
function deleteTodo(id) {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'api/delete.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  
  xhr.onload = function() {
    if (xhr.status === 200) {
      // Reload todos with current filter
      var activeTab = document.querySelector('.filter-tab.active');
      var filter = activeTab ? activeTab.dataset.filter : 'all';
      loadTodos(filter);
    } else {
      console.error('Failed to delete todo');
    }
  };
  
  xhr.send('id=' + encodeURIComponent(id));
}

// Attach event listeners to elements
function attachEventListeners() {
  // Toggle checkboxes
  var checkboxes = document.querySelectorAll('.toggle-checkbox');
  checkboxes.forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
      var li = this.closest('li');
      var id = li.dataset.id;
      var isCompleted = this.checked;
      
      // Update UI immediately
      if (isCompleted) {
        li.classList.add('completed');
      } else {
        li.classList.remove('completed');
      }
      
      toggleTodo(id, isCompleted);
    });
  });
  
  // Delete buttons
  var deleteButtons = document.querySelectorAll('.delete-btn');
  deleteButtons.forEach(function(button) {
    button.addEventListener('click', function() {
      var li = this.closest('li');
      var id = li.dataset.id;
      
      if (confirm('Are you sure you want to delete this todo?')) {
        deleteTodo(id);
      }
    });
  });
}

// Initialize filter tabs
function initFilterTabs() {
  var tabs = document.querySelectorAll('.filter-tab');
  
  tabs.forEach(function(tab) {
    tab.addEventListener('click', function() {
      // Remove active class from all tabs
      tabs.forEach(function(t) {
        t.classList.remove('active');
      });
      
      // Add active class to clicked tab
      this.classList.add('active');
      
      // Get filter value
      var filter = this.dataset.filter;
      
      // Update URL hash
      window.location.hash = filter;
      
      // Load todos with selected filter
      loadTodos(filter);
    });
  });
  
  // Check URL hash on initial load
  var hash = window.location.hash.substring(1);
  if (hash) {
    var tab = document.querySelector('.filter-tab[data-filter="' + hash + '"]');
    if (tab) {
      tab.click();
    }
  }
}

// Initialize the app
document.addEventListener('DOMContentLoaded', function() {
  // Load initial todos
  loadTodos();
  
  // Initialize filter tabs
  initFilterTabs();
  
  // Handle form submission
  var form = document.getElementById('todo-form');
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var input = document.getElementById('todo-input');
      var title = input.value.trim();
      
      if (title) {
        addTodo(title);
      }
    });
  }
});
```