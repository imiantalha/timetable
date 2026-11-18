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
            if (isset($assignment->session_number)) {
                return collect([$assignment]);
            }

            $count = max(1, (int) ($sessionCounts[$assignment->course_id] ?? 1));

            return collect(range(1, $count))->map(function (int $sessionNumber) use ($assignment): object {
                $expanded = clone $assignment;
                $expanded->session_number = $sessionNumber;

                return $expanded;
            });
        })->values();
    }
}
