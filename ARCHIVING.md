# Archiving Feature - Complete Implementation

## ✅ All Requirements Met

### 1. Tasks are archivable by owners
- ✅ Only owners and admins can archive tasks
- ✅ Archive button appears in task list for authorized users
- ✅ Business rule: Cannot archive tasks with multiple assigned users

### 2. Archived tasks are NOT permanently deleted
- ✅ Using Laravel's **SoftDeletes** trait
- ✅ Tasks have `deleted_at` timestamp instead of being removed from database
- ✅ Data is preserved and can be queried with `onlyTrashed()` or `withTrashed()`

### 3. Archived tasks are restorable
- ✅ **Restore** functionality implemented in `TaskController@restore`
- ✅ Only owners and admins can restore archived tasks
- ✅ Restored tasks return to active task list

### 4. Archived tasks do NOT appear in default listings
- ✅ Default queries automatically exclude soft-deleted records
- ✅ Task index shows only active (non-archived) tasks
- ✅ Separate "Archived Tasks" page to view archived items

## Implementation Details

### Database
```php
// Migration includes deleted_at column
$table->softDeletes();
```

### Model
```php
// Task.php
use SoftDeletes;
```

### Controller Methods
- `destroy()` - Archives task (soft delete)
- `restore()` - Restores archived task
- `archived()` - Lists all archived tasks

### Routes
- `DELETE /tasks/{task}` - Archive task
- `POST /tasks/{task}/restore` - Restore task
- `GET /tasks/archived` - View archived tasks

### Views
- **tasks/index.blade.php** - Active tasks only
- **tasks/archived.blade.php** - Archived tasks with restore button
- **layouts/navbar.blade.php** - "Archived" link added

## How to Use

### Archive a Task
1. Go to task list
2. Click the **Archive** button (🗄️ icon) on a task you own
3. Confirm the action
4. Task is removed from active list

### View Archived Tasks
1. Click **"Archived"** in the navigation bar
2. See all archived tasks you created or are assigned to
3. Admins see all archived tasks

### Restore a Task
1. Go to **Archived Tasks** page
2. Click **"Restore"** button on a task you own
3. Task returns to active task list

## Permissions

| Role  | Archive | Restore | View Archived |
|-------|---------|---------|---------------|
| Admin | ✅ All  | ✅ All  | ✅ All        |
| Head  | ✅ Own  | ✅ Own  | ✅ Own/Assigned |
| User  | ✅ Own  | ✅ Own  | ✅ Own/Assigned |

**Note:** Can only archive tasks with 1 assigned user (business rule)
