<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'due_date' => 'required|date|after_or_equal:today',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
        ];
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
            'assigned_users.*.exists' => 'One or more selected users do not exist.',
        ];
    }
}
