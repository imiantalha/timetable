<?php

declare(strict_types=1);

namespace App\Domain\Scheduling;

use App\Models\Course;
use Illuminate\Support\Collection;

final class GenerationPlan
{
    /**
     * Expand selected courses into their configured weekly sessions.
     *
     * @return Collection<int, object>
     */
    public static function expandSessions(Collection $assignments): Collection
    {
        $courses = Course::query()
            ->whereIn('id', $assignments->pluck('course_id')->unique())
            ->get(['id', 'sessions_per_week', 'duration_minutes'])
            ->keyBy('id');

        return $assignments->flatMap(function ($assignment) use ($courses): Collection {
            $course = $courses->get($assignment->course_id);
            $count = max(1, (int) ($course?->sessions_per_week ?? 1));

            if (isset($assignment->session_number)) {
                $expanded = clone $assignment;
                $expanded->sessions_per_week = $count;
                $expanded->duration_minutes = (int) ($course?->duration_minutes ?? 60);

                return collect([$expanded]);
            }

            return collect(range(1, $count))->map(function (int $sessionNumber) use ($assignment, $count, $course): object {
                $expanded = clone $assignment;
                $expanded->session_number = $sessionNumber;
                $expanded->sessions_per_week = $count;
                $expanded->duration_minutes = (int) ($course?->duration_minutes ?? 60);

                return $expanded;
            });
        })->values();
    }
}
