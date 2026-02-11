# Task Management System

A multi-user Laravel-based task management system with role-based access control (RBAC), task assignment, and archiving functionality.

## 🚀 Features

- **Role-Based Access Control**:
  - **Admin**: Full system access. Can manage all tasks and users.
  - **Head**: Can create tasks, assign them to others, and manage tasks they own.
  - **User**: Can create tasks for themselves and update the status of tasks assigned to them.
- **Task Management**:
  - Full CRUD for tasks.
  - Many-to-many task assignment (Contributors).
  - Filtering: Users see only tasks they own or are assigned to.
- **Modern UI**:
  - Clean interface with **Bootstrap 5**.
  - **SweetAlert2** integration for beautiful notifications and confirmation dialogs.
  - Optimized "Assigned To" columns (excludes owner to reduce clutter).
- **Business Logic**:
  - Tasks cannot be marked as "Completed" if the due date is in the future (unless by owner/admin).
  - Regular users can ONLY update the status of assigned tasks (other fields are read-only).
  - Only owners can modify the due date of a task.
- **Archiving (Soft Deletes)**:
  - Tasks can be archived (soft-deleted) by owners or admins.
  - Separate archive view to restore archived tasks.
  - Archiving is non-destructive and reversible.

## 🛠️ Tech Stack

- **Backend**: Laravel 12.x
- **Database**: MySQL
- **Frontend**: Blade, CSS (Vanilla), Bootstrap 5 (CDN)
- **Library**: SweetAlert2 (for alerts/confirms)
- **Auth**: Custom Manual Implementation

## 📥 Installation

1. **Clone & Install Dependencies**
   ```bash
   composer install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   # Update DB_DATABASE, DB_USERNAME, DB_PASSWORD in .env
   php artisan key:generate
   ```

3. **Database Setup**
   ```bash
   # Create a database named 'tk'
   php artisan migrate
   php artisan db:seed
   ```

4. **Running Locally**
   ```bash
   php artisan serve
   ```
   Access at: `http://localhost:8000`

## 🔑 Test Credentials (Seeders)

| Role  | Email | Password |
|-------|-------|----------|
| Admin | `admin@example.com` | `password` |
| Head  | `head@example.com`  | `password` |
| User  | `john@example.com`  | `password` |
| User  | `jane@example.com`  | `password` |

## 📂 Project Structure

- `app/Http/Controllers/TaskController.php`: Main logic for task operations and authorization.
- `app/Services/TaskService.php`: Business logic layer (permissions, rules).
- `app/Http/Requests/`: Form validation for tasks.
- `app/Models/Task.php`: SoftDeletes and many-to-many relationships.
- `resources/views/layouts/app.blade.php`: Main layout with SweetAlert2 handlers.
- `resources/views/tasks/`: Blade templates for the task management UI.


