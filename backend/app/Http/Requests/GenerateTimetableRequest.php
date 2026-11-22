<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $assignments = collect($this->input('assignments', []));
                $courses = Course::query()
                    ->whereIn('id', $assignments->pluck('course_id')->unique())
                    ->get(['id', 'sessions_per_week'])
                    ->keyBy('id');

                $seenSessions = [];

                foreach ($assignments as $index => $assignment) {
                    $course = $courses->get($assignment['course_id']);
                    if (! $course) {
                        continue;
                    }

                    $sessionNumbers = isset($assignment['session_number'])
                        ? [(int) $assignment['session_number']]
                        : range(1, (int) $course->sessions_per_week);

                    foreach ($sessionNumbers as $sessionNumber) {
                        if ($sessionNumber > (int) $course->sessions_per_week) {
                            $validator->errors()->add(
                                "assignments.{$index}.session_number",
                                'The session number cannot exceed the course weekly session count.',
                            );
                            continue;
                        }

                        $key = implode(':', [
                            $assignment['course_id'],
                            $assignment['section_id'],
                            $sessionNumber,
                        ]);

                        if (isset($seenSessions[$key])) {
                            $validator->errors()->add(
                                "assignments.{$index}.course_id",
                                'The same course session cannot be assigned more than once to a section.',
                            );
                            continue;
                        }

                        $seenSessions[$key] = true;
                    }
                }
            },
        ];
    }
}
