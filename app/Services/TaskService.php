<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TaskService
{
    /**
     * Get tasks for the authenticated user based on their role
     */
    public function getTasksForUser(User $user)
    {
        if ($user->isAdmin()) {
            return Task::with(['creator', 'assignedUsers'])->latest()->get();
        } elseif ($user->isHead()) {
            return Task::where(function ($query) use ($user) {
                $query->ownedBy($user)
                    ->orWhereHas('assignedUsers', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })->with(['creator', 'assignedUsers'])->latest()->get();
        } else {
            return Task::where(function ($query) use ($user) {
                $query->ownedBy($user)
                    ->orWhereHas('assignedUsers', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })->with(['creator', 'assignedUsers'])->latest()->get();
        }
    }

    /**
     * Create a new task with assigned users
     */
    public function createTask(array $data, User $creator)
    {
        return DB::transaction(function () use ($data, $creator) {
            $task = Task::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'],
                'due_date' => $data['due_date'],
                'created_by' => $creator->id,
            ]);

            // Attach owner to task_user pivot
            $task->assignedUsers()->attach($creator->id, ['role' => 'owner']);

            // Attach other assigned users as contributors
            if (!empty($data['assigned_users'])) {
                foreach ($data['assigned_users'] as $userId) {
                    if ($userId != $creator->id) {
                        $task->assignedUsers()->attach($userId, ['role' => 'contributor']);
                    }
                }
            }

            return $task;
        });
    }

    /**
     * Update an existing task
     */
    public function updateTask(Task $task, array $data, User $user)
    {
        return DB::transaction(function () use ($task, $data, $user) {
            // Update task fields based on user permissions
            if ($task->isOwner($user) || $user->isAdmin() || $user->isHead()) {
                $task->update($data);

                // Update assigned users if provided
                if (isset($data['assigned_users'])) {
                    $this->syncAssignedUsers($task, $data['assigned_users'], $user);
                }
            } else {
                // Contributors can only update status
                $task->update(['status' => $data['status']]);
            }

            return $task;
        });
    }

    /**
     * Sync assigned users for a task
     */
    protected function syncAssignedUsers(Task $task, array $assignedUsers, User $owner)
    {
        // Keep owner
        $syncData = [$owner->id => ['role' => 'owner']];

        // Add contributors
        foreach ($assignedUsers as $userId) {
            if ($userId != $owner->id) {
                $syncData[$userId] = ['role' => 'contributor'];
            }
        }

        $task->assignedUsers()->sync($syncData);
    }

    /**
     * Check if user can view a task
     */
    public function canViewTask(Task $task, User $user): bool
    {
        return $user->isAdmin() || $task->isAssignedTo($user) || $task->isOwner($user);
    }

    /**
     * Check if user can edit a task
     */
    public function canEditTask(Task $task, User $user): bool
    {
        return $user->isAdmin() ||
            $task->isOwner($user) ||
            ($user->isHead() && $task->created_by === $user->id) ||
            $task->isAssignedTo($user); // Regular users can edit (update status) if assigned
    }

    /**
     * Check if user can delete/archive a task
     */
    public function canDeleteTask(Task $task, User $user): bool
    {
        // Any owner or admin can archive
        return $task->isOwner($user) || $user->isAdmin();
    }

    /**
     * Validate business rule: Cannot mark as completed if due date is in future
     */
    public function canMarkAsCompleted(Task $task, string $status, User $user): bool
    {
        if ($status !== 'completed') {
            return true;
        }

        // Owner and admin can mark as completed anytime
        if ($task->isOwner($user) || $user->isAdmin()) {
            return true;
        }

        // Others can only mark as completed if due date has passed
        return !$task->due_date->isFuture();
    }

    /**
     * Archive (soft delete) a task
     */
    public function archiveTask(Task $task)
    {
        return $task->delete();
    }

    /**
     * Restore an archived task
     */
    public function restoreTask(Task $task)
    {
        return $task->restore();
    }
}
