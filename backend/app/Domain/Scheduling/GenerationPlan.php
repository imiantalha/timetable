<?php

declare(strict_types=1);

namespace App\Domain\Scheduling;

use App\Models\Course;
use Illuminate\Support\Collection;

final class GenerationPlan
{
    /**
     * Expand each selected course into the number of sessions configured for it.
     * Explicit session numbers are treated as already-expanded assignments.
     */
    public static function expandSessions(Collection $assignments): Collection
    {
        $sessionCounts = Course::query()
            ->whereIn('id', $assignments->pluck('course_id')->unique())
            ->pluck('sessions_per_week', 'id');

        return $assignments->flatMap(function ($assignment) use ($sessionCounts): Collection {
            $count = max(1, (int) ($sessionCounts[$assignment->course_id] ?? 1));

            if (isset($assignment->session_number)) {
                $expanded = clone $assignment;
                $expanded->sessions_per_week = $count;

                return collect([$expanded]);
            }

            return collect(range(1, $count))->map(function (int $sessionNumber) use ($assignment, $count): object {
                $expanded = clone $assignment;
                $expanded->session_number = $sessionNumber;
                $expanded->sessions_per_week = $count;

                return $expanded;
            });
        })->values();
    }
}
