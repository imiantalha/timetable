<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $department = Department::find($this->input('department_id'));

        return $user?->role === 'admin'
            && $department !== null
            && ($user->institution_id === null || (int) $department->institution_id === (int) $user->institution_id);
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'credits' => ['required', 'integer', 'min:1', 'max:12'],
            'sessions_per_week' => ['required', 'integer', 'min:1', 'max:14'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:240'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
