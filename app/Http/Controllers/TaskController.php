<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index()
    {
        $tasks = $this->taskService->getTasksForUser(auth()->user());
        return view('tasks.index', compact('tasks'));
    }

    public function archived()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $tasks = Task::onlyTrashed()->with(['creator', 'assignedUsers'])->latest()->get();
        } else {
            $tasks = Task::onlyTrashed()->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('assignedUsers', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })->with(['creator', 'assignedUsers'])->latest()->get();
        }

        return view('tasks.archived', compact('tasks'));
    }

    public function create()
    {
        $users = User::where('id', '!=', auth()->id())->get();
        return view('tasks.create', compact('users'));
    }

    public function store(StoreTaskRequest $request)
    {
        $this->taskService->createTask($request->validated(), auth()->user());
        return redirect()->route('tasks.index')->with('success', 'Task created successfully!');
    }

    public function show(Task $task)
    {
        if (!$this->taskService->canViewTask($task, auth()->user())) {
            abort(403, 'You are not authorized to view this task.');
        }

        $task->load(['creator', 'assignedUsers']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $user = auth()->user();

        // Allow if: admin, owner, head who created it, or assigned user
        if (!$this->taskService->canEditTask($task, $user)) {
            abort(403, 'You are not authorized to edit this task.');
        }

        $users = User::where('id', '!=', auth()->id())->get();
        $task->load('assignedUsers');

        return view('tasks.edit', compact('task', 'users'));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $user = auth()->user();

        // Check if user can edit this task
        if (!$this->taskService->canEditTask($task, $user)) {
            abort(403, 'You are not authorized to edit this task.');
        }

        $validated = $request->validated();

        // Business Rule: Cannot mark as completed if due date is in future (unless owner/admin)
        if (!$this->taskService->canMarkAsCompleted($task, $validated['status'], $user)) {
            return back()->withErrors(['status' => 'Cannot mark task as completed before due date.']);
        }

        $this->taskService->updateTask($task, $validated, $user);
        return redirect()->route('tasks.index')->with('success', 'Task updated successfully!');
    }

    public function destroy(Task $task)
    {
        $user = auth()->user();

        // Any owner or admin can archive a task
        if (!$task->isOwner($user) && !$user->isAdmin()) {
            abort(403, 'You are not authorized to archive this task.');
        }

        // Archiving (soft delete) is allowed regardless of assigned user count
        // as it is reversible and non-destructive.

        $this->taskService->archiveTask($task);
        return redirect()->route('tasks.index')->with('success', 'Task archived successfully!');
    }

    public function restore($id)
    {
        $task = Task::withTrashed()->findOrFail($id);

        if (!$task->isOwner(auth()->user()) && !auth()->user()->isAdmin()) {
            abort(403, 'You are not authorized to restore this task.');
        }

        $this->taskService->restoreTask($task);
        return redirect()->route('tasks.index')->with('success', 'Task restored successfully!');
    }
}
