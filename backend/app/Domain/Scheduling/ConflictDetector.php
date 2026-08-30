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
        if (! $slot) return $conflicts;

        $overlapping = TimetableEntry::query()
            ->where('timetable_id', $entry->timetable_id)
            ->where('id', '!=', $entry->id)
            ->where('time_slot_id', $slot->id)
            ->with(['teacher', 'section', 'room'])
            ->get();

        foreach ($overlapping as $other) {
            if ($other->teacher_id === $entry->teacher_id) {
                $conflicts->push(['type' => 'teacher_overlap', 'message' => 'Teacher is already scheduled in this time slot.', 'entry_id' => $other->id]);
            }
            if ($other->section_id === $entry->section_id) {
                $conflicts->push(['type' => 'section_overlap', 'message' => 'Section is already scheduled in this time slot.', 'entry_id' => $other->id]);
            }
            if ($other->room_id === $entry->room_id) {
                $conflicts->push(['type' => 'room_overlap', 'message' => 'Room is already occupied in this time slot.', 'entry_id' => $other->id]);
            }
        }

        if ($entry->room && $entry->section && $entry->room->capacity < $entry->section->capacity) {
            $conflicts->push(['type' => 'room_capacity', 'message' => 'Room capacity is smaller than section capacity.', 'entry_id' => $entry->id]);
        }

        return $conflicts->unique(fn (array $conflict) => $conflict['type'].'-'.$conflict['entry_id'])->values();
    }
}
