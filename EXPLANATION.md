# Technical Explanation

## 1. Many-to-Many Relationship Modeling

The relationship between tasks and users is modeled using a **pivot table** (`task_user`) with an additional `role` column:

```php
// Task Model
public function assignedUsers()
{
    return $this->belongsToMany(User::class, 'task_user')
                ->withPivot('role')
                ->withTimestamps();
}

// User Model
public function assignedTasks()
{
    return $this->belongsToMany(Task::class, 'task_user')
                ->withPivot('role')
                ->withTimestamps();
}
```

**Key Design Decisions:**
- The `tasks` table has a `created_by` foreign key to track the task owner
- The `task_user` pivot table stores the relationship with a `role` column (owner/contributor)
- When a task is created, the creator is automatically attached to the pivot table with `role = 'owner'`
- Additional users are attached with `role = 'contributor'`
- This dual approach (FK + pivot) allows efficient querying while maintaining clear ownership semantics

## 2. Authorization Enforcement

Authorization is enforced at **multiple layers**:

### Controller-Level Authorization
- Each controller method checks user permissions before executing actions
- Example from `TaskController`:
  ```php
  if (!$user->isAdmin() && !$task->isAssignedTo($user) && !$task->isOwner($user)) {
      abort(403, 'You are not authorized to view this task.');
  }
  ```

### Model-Level Helpers
- User model has role-checking methods: `isAdmin()`, `isHead()`, `isUser()`
- Task model has relationship methods: `isOwner($user)`, `isAssignedTo($user)`

### View-Level Authorization
- Blade templates use `@if` directives to conditionally show/hide UI elements based on user role and task ownership
- Example: Only owners see the "Edit" button for tasks they created

### Query Scoping
- Tasks are filtered at the database level based on user role:
  - **Admin**: Sees all tasks
  - **Head**: Sees tasks they created or are assigned to
  - **User**: Sees tasks they created or are assigned to

## 3. Business Rules Placement

Business rules are enforced in the **Controller layer** for the following reasons:

### Location: `TaskController@update()`
```php
// Rule: Cannot mark as completed if due date is in future (unless owner/admin)
if ($validated['status'] === 'completed' && 
    $task->due_date->isFuture() && 
    !$task->isOwner($user) && 
    !$user->isAdmin()) {
    return back()->withErrors(['status' => 'Cannot mark task as completed before due date.']);
}
```

### Location: `TaskController@destroy()`
```php
// Rule: Cannot delete if more than one assigned user
if ($task->assignedUsers()->count() > 1) {
    return back()->withErrors(['error' => 'Cannot delete task with multiple assigned users.']);
}
```

### Location: Validation Rules (Dynamic)
```php
// Rule: Only owner can change due date
if ($task->isOwner($user) || $user->isAdmin()) {
    $rules['due_date'] = 'required|date|after_or_equal:today';
}
```

**Rationale:**
- Controllers are the natural place for business logic as they handle HTTP requests and responses
- Validation rules are context-aware (different for owners vs contributors)
- Error messages are returned directly to the user via the web interface
- This approach keeps models focused on data relationships and keeps business logic testable

## 4. One Improvement with More Time

**Implement Laravel Policies for cleaner authorization:**

Currently, authorization logic is scattered across controller methods. With more time, I would:

1. Create a `TaskPolicy` with methods like:
   - `view(User $user, Task $task)`
   - `update(User $user, Task $task)`
   - `delete(User $user, Task $task)`
   - `changeStatus(User $user, Task $task)`
   - `changeDueDate(User $user, Task $task)`

2. Register the policy in `AuthServiceProvider`

3. Use policy methods in controllers:
   ```php
   $this->authorize('update', $task);
   ```

4. Use policies in Blade templates:
   ```blade
   @can('update', $task)
       <a href="{{ route('tasks.edit', $task) }}">Edit</a>
   @endcan
   ```

**Benefits:**
- Centralized authorization logic
- Easier to test and maintain
- Cleaner controller code
- Consistent authorization across the application
- Better separation of concerns

---

**Additional Improvements:**
- Add automated tests (Feature tests for each role)
- Implement task filtering/search functionality
- Add email notifications for task assignments
- Create an API layer for mobile/SPA integration
- Add task comments/activity log
