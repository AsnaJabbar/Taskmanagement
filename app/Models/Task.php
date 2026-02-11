<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    // Relationship: Task belongs to a creator (owner)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship: Task has many assigned users (many-to-many)
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    // Scope: Tasks assigned to a specific user
    public function scopeAssignedTo($query, $user)
    {
        return $query->whereHas('assignedUsers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    // Scope: Tasks owned/created by a specific user
    public function scopeOwnedBy($query, $user)
    {
        return $query->where('created_by', $user->id);
    }

    // Helper: Check if user is the owner
    public function isOwner($user)
    {
        return $this->created_by === $user->id;
    }

    // Helper: Check if user is assigned to this task
    public function isAssignedTo($user)
    {
        return $this->assignedUsers->contains($user->id);
    }
}
