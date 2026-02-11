@extends('layouts.app')

@section('title', 'Archived Tasks')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-archive"></i> Archived Tasks</h2>
            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Tasks
            </a>
        </div>

        @if($tasks->isEmpty())
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No archived tasks found.
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
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr>
                                <td>
                                    <strong>{{ $task->title }}</strong>
                                    <span class="badge bg-secondary">Archived</span>
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
                                <td>{{ $task->due_date->format('M d, Y') }}</td>
                                <td>{{ $task->creator->name }}</td>
                                <td>{{ $task->deleted_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($task->isOwner(auth()->user()) || auth()->user()->isAdmin())
                                        <form action="{{ route('tasks.restore', $task->id) }}" method="POST"
                                            class="d-inline delete-confirm" data-message="Are you sure you want to restore this task?">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection