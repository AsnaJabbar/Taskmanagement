<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $task = $this->route('task');
        $user = auth()->user();

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
        ];

        // Only owner or admin can change due date
        if ($task->isOwner($user) || $user->isAdmin()) {
            $rules['due_date'] = 'required|date|after_or_equal:today';
        }

        // If user is just a contributor, they can only update status
        if (!$task->isOwner($user) && !$user->isAdmin() && !$user->isHead()) {
            $rules = ['status' => 'required|in:pending,in_progress,completed'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The task title is required.',
            'status.required' => 'Please select a task status.',
            'status.in' => 'Invalid status selected.',
            'due_date.required' => 'Please specify a due date.',
            'due_date.after_or_equal' => 'Due date must be today or a future date.',
        ];
    }
}
