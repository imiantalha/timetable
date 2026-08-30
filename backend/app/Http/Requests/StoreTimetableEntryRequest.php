<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTimetableEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'timetable_id' => ['required','integer','exists:timetables,id'],
            'course_id' => ['required','integer','exists:courses,id'],
            'teacher_id' => ['required','integer','exists:teachers,id'],
            'section_id' => ['required','integer','exists:sections,id'],
            'room_id' => ['required','integer','exists:rooms,id'],
            'time_slot_id' => ['required','integer','exists:time_slots,id'],
            'session_number' => ['required','integer','min:1'],
        ];
    }
}
