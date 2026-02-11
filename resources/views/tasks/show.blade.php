@extends('layouts.app')

@section('title', $task->title)

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-eye"></i> Task Details</h4>
                    <div>
                        @if($task->isOwner(auth()->user()) || auth()->user()->isAdmin() || (auth()->user()->isHead() && $task->created_by === auth()->id()))
                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h3>{{ $task->title }}</h3>
                        <div class="d-flex gap-2 mb-3">
                            @if($task->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($task->status === 'in_progress')
                                <span class="badge bg-info">In Progress</span>
                            @else
                                <span class="badge bg-success">Completed</span>
                            @endif
                            
                            @if($task->trashed())
                                <span class="badge bg-secondary">Archived</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">Description</h6>
                        <p>{{ $task->description ?? 'No description provided.' }}</p>
                    </div>
                    
                    <hr>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Due Date</h6>
                            <p>
                                <i class="bi bi-calendar"></i> {{ $task->due_date->format('F d, Y') }}
                                @if($task->due_date->isPast() && $task->status !== 'completed')
                                    <span class="badge bg-danger">Overdue</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Created By (Owner)</h6>
                            <p>
                                <i class="bi bi-person-circle"></i> {{ $task->creator->name }}
                                <span class="badge bg-secondary">{{ ucfirst($task->creator->role) }}</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted">Assigned Users</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($task->assignedUsers->where('id', '!=', $task->created_by) as $user)
                                <div class="badge bg-light text-dark border">
                                    <i class="bi bi-person"></i> {{ $user->name }}
                                    <span class="badge bg-secondary">Contributor</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-muted small">
                        <p class="mb-1"><strong>Created:</strong> {{ $task->created_at->format('M d, Y h:i A') }}</p>
                        <p class="mb-0"><strong>Last Updated:</strong> {{ $task->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Tasks
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
