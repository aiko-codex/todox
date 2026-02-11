# Todox — Product Requirements Document

## 1. Overview

**Todox** is a simple, lightweight todo application for managing daily tasks. It runs entirely as a local PHP application with a SQLite database — no external services or complex frameworks required.

### Tech Stack

| Layer      | Technology       |
|------------|------------------|
| Backend    | PHP (vanilla)    |
| Frontend   | HTML, CSS, JS    |
| Database   | SQLite           |
| Server     | PHP built-in dev server (`php -S`) |

---

## 2. Goals

- Provide a clean, minimal interface for managing todos
- Zero-configuration setup — just run `php -S localhost:8000` and go
- No frameworks, no build tools, no dependencies
- Single-user, local-first application

---

## 3. Features

### 3.1 Core Features (MVP)

#### Add a Todo

- User can type a task title and submit it
- New todo appears at the top of the list
- Empty submissions are prevented (client + server validation)

#### View Todos

- All todos are displayed in a single list
- Each todo shows: title, created date, and status (pending / completed)
- Todos are ordered by creation date (newest first)

#### Complete a Todo

- User can mark a todo as completed by clicking a checkbox or button
- Completed todos are visually distinct (strikethrough + muted color)
- User can also unmark a completed todo back to pending

#### Delete a Todo

- User can delete a todo permanently
- A confirmation prompt appears before deletion

#### Filter Todos

- User can filter the list by: **All**, **Pending**, **Completed**
- Active filter is visually highlighted

### 3.2 Nice-to-Have (Post-MVP)

- Edit a todo title inline
- Due date for each todo
- Priority levels (Low, Medium, High)
- Search/filter by keyword
- Dark mode toggle
- Drag-and-drop reordering

---

## 4. Database Schema

**Database file:** `database/todox.db` (SQLite)

### `todos` Table

| Column       | Type         | Constraints                        |
|--------------|--------------|------------------------------------|
| `id`         | INTEGER      | PRIMARY KEY, AUTOINCREMENT         |
| `title`      | TEXT         | NOT NULL                           |
| `is_completed` | INTEGER    | NOT NULL, DEFAULT 0 (0=pending, 1=completed) |
| `created_at` | TEXT         | NOT NULL, DEFAULT CURRENT_TIMESTAMP |
| `updated_at` | TEXT         | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

---

## 5. Application Structure

```
todox/
├── PRD.md                  # This document
├── index.php               # Entry point — renders the main page
├── database/
│   └── todox.db            # SQLite database file (auto-created)
├── api/
│   ├── connect.php         # Database connection & initialization
│   ├── add.php             # POST — add a new todo
│   ├── toggle.php          # POST — toggle todo completion status
│   ├── delete.php          # POST — delete a todo
│   └── list.php            # GET  — fetch all todos (with optional filter)
├── assets/
│   ├── css/
│   │   └── style.css       # All styles
│   └── js/
│       └── app.js          # All client-side logic
└── includes/
    └── functions.php       # Shared PHP helper functions
```

---

## 6. API Endpoints

All API endpoints live under `/api/` and return JSON responses.

### `GET /api/list.php`

- **Query params:** `?filter=all|pending|completed` (default: `all`)
- **Response:** `{ "success": true, "todos": [...] }`

### `POST /api/add.php`

- **Body:** `{ "title": "Buy groceries" }`
- **Response:** `{ "success": true, "todo": { ... } }`
- **Errors:** `{ "success": false, "error": "Title is required" }`

### `POST /api/toggle.php`

- **Body:** `{ "id": 1 }`
- **Response:** `{ "success": true, "todo": { ... } }`

### `POST /api/delete.php`

- **Body:** `{ "id": 1 }`
- **Response:** `{ "success": true }`

---

## 7. UI / UX Requirements

### Layout

- Single-page layout, centered container (max-width ~600px)
- Clean white card on a subtle background
- Responsive — works on mobile and desktop

### Components

1. **Header** — App title "Todox" with a simple tagline
2. **Input bar** — Text input + "Add" button, always visible at the top
3. **Filter tabs** — All | Pending | Completed
4. **Todo list** — Scrollable list of todo items
5. **Todo item** — Checkbox, title, date, delete button
6. **Empty state** — Friendly message when no todos match the filter
7. **Footer** — Minimal, e.g. "Todox © 2026"

### Interactions

- Adding a todo: AJAX POST → prepend to list (no full page reload)
- Toggling complete: AJAX POST → update item styling in-place
- Deleting a todo: Confirm dialog → AJAX POST → remove item with fade-out
- Filtering: Client-side filter (no server round-trip needed)

### Visual Style

- Modern, minimal aesthetic
- Soft rounded corners, subtle shadows
- Smooth transitions and micro-animations
- Color palette: neutral base with an accent color for CTAs
- Typography: System font stack or a clean sans-serif (e.g., Inter)

---

## 8. Non-Functional Requirements

| Requirement    | Details                                      |
|----------------|----------------------------------------------|
| Performance    | Page load under 1 second (local)             |
| Browser Support| Modern browsers (Chrome, Firefox, Edge, Safari) |
| Security       | Input sanitization, prepared SQL statements  |
| Data Storage   | SQLite file — no external database needed    |
| Deployment     | `php -S localhost:8000` — that's it          |

---

## 9. Development Workflow

1. **Setup** — Clone repo, run `php -S localhost:8000`
2. **Database** — Auto-created on first request via `connect.php`
3. **Development** — Edit PHP/HTML/CSS/JS, refresh browser
4. **Testing** — Manual testing in browser + verify API responses

---

## 10. Success Criteria

- [ ] User can add a todo with a title
- [ ] User can view all todos in a list
- [ ] User can mark a todo as completed / uncompleted
- [ ] User can delete a todo
- [ ] User can filter todos by status
- [ ] All operations work without full page reload (AJAX)
- [ ] SQLite database is auto-created on first run
- [ ] Application runs with zero external dependencies
