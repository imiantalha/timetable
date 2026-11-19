<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user?->role !== 'admin') {
            return false;
        }

        $departmentId = $this->input('department_id');
        $department = $departmentId === null ? null : Department::find($departmentId);

        return $department === null
            || $user->institution_id === null
            || (int) $department->institution_id === (int) $user->institution_id;
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
