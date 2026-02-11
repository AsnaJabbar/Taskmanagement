<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Head User
        $head = User::create([
            'name' => 'Head User',
            'email' => 'head@example.com',
            'password' => Hash::make('password'),
            'role' => 'head',
        ]);

        // Create Regular Users
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Create Sample Tasks
        // Task 1: Created by Head, assigned to multiple users
        $task1 = Task::create([
            'title' => 'Complete Project Documentation',
            'description' => 'Write comprehensive documentation for the new feature.',
            'status' => 'in_progress',
            'due_date' => now()->addDays(7),
            'created_by' => $head->id,
        ]);
        $task1->assignedUsers()->attach([
            $head->id => ['role' => 'owner'],
            $user1->id => ['role' => 'contributor'],
            $user2->id => ['role' => 'contributor'],
        ]);

        // Task 2: Created by User1, only assigned to themselves
        $task2 = Task::create([
            'title' => 'Review Code Changes',
            'description' => 'Review the latest pull requests.',
            'status' => 'pending',
            'due_date' => now()->addDays(3),
            'created_by' => $user1->id,
        ]);
        $task2->assignedUsers()->attach($user1->id, ['role' => 'owner']);

        // Task 3: Created by Admin, assigned to Head
        $task3 = Task::create([
            'title' => 'Prepare Monthly Report',
            'description' => 'Compile and submit the monthly performance report.',
            'status' => 'pending',
            'due_date' => now()->addDays(14),
            'created_by' => $admin->id,
        ]);
        $task3->assignedUsers()->attach([
            $admin->id => ['role' => 'owner'],
            $head->id => ['role' => 'contributor'],
        ]);

        // Task 4: Overdue task
        $task4 = Task::create([
            'title' => 'Fix Critical Bug',
            'description' => 'Address the security vulnerability reported last week.',
            'status' => 'in_progress',
            'due_date' => now()->subDays(2),
            'created_by' => $head->id,
        ]);
        $task4->assignedUsers()->attach([
            $head->id => ['role' => 'owner'],
            $user1->id => ['role' => 'contributor'],
        ]);

        // Task 5: Completed task
        $task5 = Task::create([
            'title' => 'Setup Development Environment',
            'description' => 'Configure local development environment for new team members.',
            'status' => 'completed',
            'due_date' => now()->subDays(5),
            'created_by' => $user2->id,
        ]);
        $task5->assignedUsers()->attach($user2->id, ['role' => 'owner']);
    }
}
