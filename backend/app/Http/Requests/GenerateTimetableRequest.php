<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class GenerateTimetableRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'admin'; }

    public function rules(): array
    {
        return [
            'assignments' => ['required','array','min:1'],
            'assignments.*.course_id' => ['required','integer','exists:courses,id'],
            'assignments.*.teacher_id' => ['required','integer','exists:teachers,id'],
            'assignments.*.section_id' => ['required','integer','exists:sections,id'],
            'assignments.*.room_id' => ['required','integer','exists:rooms,id'],
            'assignments.*.session_number' => ['sometimes','integer','min:1'],
            'time_slot_ids' => ['required','array','min:1'],
            'time_slot_ids.*' => ['integer','exists:time_slots,id'],
        ];
    }
}
