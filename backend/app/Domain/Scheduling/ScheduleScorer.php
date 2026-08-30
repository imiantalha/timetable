<?php

declare(strict_types=1);

namespace App\Domain\Scheduling;

use Illuminate\Support\Collection;

final class ScheduleScorer
{
    /** Lower score is better. Penalizes unplaced assignments and timetable gaps. */
    public function score(Collection $entries, Collection $unplaced): int
    {
        $score = $unplaced->count() * 1000;
        $bySection = $entries->groupBy('section_id');

        foreach ($bySection as $sectionEntries) {
            $days = $sectionEntries->groupBy(fn ($entry) => $entry->timeSlot->day_of_week ?? 0);
            foreach ($days as $dayEntries) {
                $slots = $dayEntries->pluck('time_slot_id')->sort()->values();
                if ($slots->count() > 1) {
                    $score += max(0, ($slots->last() - $slots->first() + 1) - $slots->count()) * 10;
                }
            }
        }

        return $score;
    }
}
