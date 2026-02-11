@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-list-task"></i> My Tasks</h2>
            @if(auth()->user()->isAdmin() || auth()->user()->isHead() || auth()->user()->isUser())
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Task
                </a>
            @endif
        </div>

        @if($tasks->isEmpty())
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No tasks found. Create your first task!
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Owner</th>
                            <th>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr>
                                <td>
                                    <strong>{{ $task->title }}</strong>
                                    @if($task->trashed())
                                        <span class="badge bg-secondary">Archived</span>
                                    @endif
                                </td>
                                <td>
                                    @if($task->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($task->status === 'in_progress')
                                        <span class="badge bg-info">In Progress</span>
                                    @else
                                        <span class="badge bg-success">Completed</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $task->due_date->format('M d, Y') }}
                                    @if($task->due_date->isPast() && $task->status !== 'completed')
                                        <i class="bi bi-exclamation-triangle text-danger" title="Overdue"></i>
                                    @endif
                                </td>
                                <td>{{ $task->creator->name }}</td>
                                <td>
                                    <small>{{ $task->assignedUsers->where('id', '!=', $task->created_by)->pluck('name')->join(', ') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if($task->isOwner(auth()->user()) || auth()->user()->isAdmin() || (auth()->user()->isHead() && $task->created_by === auth()->id()) || $task->isAssignedTo(auth()->user()))
                                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif

                                        @if($task->isOwner(auth()->user()) || auth()->user()->isAdmin())
                                            <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                                class="d-inline delete-confirm"
                                                data-message="Are you sure you want to archive this task?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Archive">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection