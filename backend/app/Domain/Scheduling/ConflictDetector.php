<?php

declare(strict_types=1);

namespace App\Domain\Scheduling;

use App\Models\TimetableEntry;
use Illuminate\Support\Collection;

final class ConflictDetector
{
    /** @return Collection<int, array{type:string,message:string,entry_id:int}> */
    public function detect(TimetableEntry $entry): Collection
    {
        $entry->loadMissing(['teacher', 'section', 'room', 'timeSlot']);

        $conflicts = collect();
        $slot = $entry->timeSlot;

        if (! $slot) {
            return $conflicts;
        }

        // Compare actual clock ranges, not only slot IDs: two separately configured
        // slots can still overlap on the same day.
        $overlapping = TimetableEntry::query()
            ->where('timetable_id', $entry->timetable_id)
            ->where('id', '!=', $entry->id)
            ->whereHas('timeSlot', function ($query) use ($slot): void {
                $query
                    ->where('day_of_week', $slot->day_of_week)
                    ->where('starts_at', '<', $slot->ends_at)
                    ->where('ends_at', '>', $slot->starts_at);
            })
            ->with(['teacher', 'section', 'room', 'timeSlot'])
            ->get();

        foreach ($overlapping as $other) {
            if ((int) $other->teacher_id === (int) $entry->teacher_id) {
                $conflicts->push([
                    'type' => 'teacher_overlap',
                    'message' => 'Teacher is already scheduled during an overlapping time.',
                    'entry_id' => $other->id,
                ]);
            }

            if ((int) $other->section_id === (int) $entry->section_id) {
                $conflicts->push([
                    'type' => 'section_overlap',
                    'message' => 'Section is already scheduled during an overlapping time.',
                    'entry_id' => $other->id,
                ]);
            }

            if ((int) $other->room_id === (int) $entry->room_id) {
                $conflicts->push([
                    'type' => 'room_overlap',
                    'message' => 'Room is already occupied during an overlapping time.',
                    'entry_id' => $other->id,
                ]);
            }
        }

        if ($entry->room && $entry->section && $entry->room->capacity < $entry->section->capacity) {
            $conflicts->push([
                'type' => 'room_capacity',
                'message' => 'Room capacity is smaller than section capacity.',
                'entry_id' => $entry->id,
            ]);
        }

        return $conflicts
            ->unique(fn (array $conflict) => $conflict['type'].'-'.$conflict['entry_id'])
            ->values();
    }
}
