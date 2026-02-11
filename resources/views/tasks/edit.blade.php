@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-pencil"></i> Edit Task</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('tasks.update', $task) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        {{-- Title field --}}
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            @if($task->isOwner(auth()->user()) || auth()->user()->isAdmin() || auth()->user()->isHead())
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title', $task->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @else
                                <input type="text" class="form-control" value="{{ $task->title }}" disabled>
                                <small class="text-muted">Only the owner can change the title.</small>
                            @endif
                        </div>
                        
                        {{-- Description field --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            @if($task->isOwner(auth()->user()) || auth()->user()->isAdmin() || auth()->user()->isHead())
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4">{{ old('description', $task->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @else
                                <textarea class="form-control" rows="4" disabled>{{ $task->description ?? 'No description' }}</textarea>
                                <small class="text-muted">Only the owner can change the description.</small>
                            @endif
                        </div>
                        
                        <div class="row">
                            {{-- Status field - Always editable for assigned users --}}
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                    <option value="pending" {{ old('status', $task->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ old('status', $task->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if(!$task->isOwner(auth()->user()) && !auth()->user()->isAdmin() && !auth()->user()->isHead())
                                    <small class="text-success">✓ You can update the status of this task.</small>
                                @endif
                            </div>
                            
                            {{-- Due date field --}}
                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                                @if($task->isOwner(auth()->user()) || auth()->user()->isAdmin())
                                    <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                           id="due_date" name="due_date" value="{{ old('due_date', $task->due_date->format('Y-m-d')) }}" 
                                           min="{{ date('Y-m-d') }}" required>
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @else
                                    <input type="text" class="form-control" value="{{ $task->due_date->format('M d, Y') }}" disabled>
                                    <small class="text-muted">Only the owner can change the due date.</small>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Assigned users - Only for owners/admins/heads --}}
                        @if($task->isOwner(auth()->user()) || auth()->user()->isAdmin() || auth()->user()->isHead())
                            <div class="mb-3">
                                <label class="form-label">Assign Users</label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($users as $user)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="assigned_users[]" value="{{ $user->id }}" 
                                                   id="user{{ $user->id }}"
                                                   {{ $task->assignedUsers->contains($user->id) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="user{{ $user->id }}">
                                                {{ $user->name }} 
                                                <span class="badge bg-secondary">{{ ucfirst($user->role) }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">You are the owner of this task.</small>
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label">Assigned Users</label>
                                <div class="border rounded p-3">
                                    @foreach($task->assignedUsers as $user)
                                        <div class="mb-1">
                                            <i class="bi bi-person"></i> {{ $user->name }}
                                            @if($user->pivot->role === 'owner')
                                                <span class="badge bg-primary">Owner</span>
                                            @else
                                                <span class="badge bg-secondary">Contributor</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Only the owner can modify assigned users.</small>
                            </div>
                        @endif
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
