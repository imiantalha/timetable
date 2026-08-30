<?php

declare(strict_types=1);

namespace App\Domain\Scheduling;

use App\Models\Course;
use Illuminate\Support\Collection;

final class GenerationPlan
{
    /** Expand each course into the number of weekly sessions it requires. */
    public function expand(Collection $courses, Collection $teachers, Collection $sections, Collection $rooms): Collection
    {
        return $courses->flatMap(function (Course $course) use ($teachers, $sections, $rooms): Collection {
            $teacher = $teachers->first(fn ($item) => $item->department_id === $course->department_id);
            $section = $sections->first(fn ($item) => $item->department_id === $course->department_id);
            if (! $teacher || ! $section || $rooms->isEmpty()) return collect();

            $room = $rooms->first(fn ($item) => $item->capacity >= $section->capacity) ?? $rooms->first();
            return collect(range(1, max(1, $course->sessions_per_week)))->map(fn (int $session) => (object) [
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
                'section_id' => $section->id,
                'room_id' => $room->id,
                'session_number' => $session,
            ]);
        })->values();
    }
}
