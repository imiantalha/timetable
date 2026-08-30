<?php

declare(strict_types=1);

namespace App\Domain\Scheduling;

use App\Models\Timetable;
use App\Models\TimetableEntry;
use Illuminate\Support\Collection;

final class TimetableGenerator
{
    public function __construct(private readonly ConflictDetector $conflictDetector) {}

    /**
     * Generate a deterministic first-fit draft. Hard conflicts are never persisted.
     * A later optimizer can replace candidate ordering without changing the API.
     */
    public function generate(Timetable $timetable, Collection $assignments, Collection $slots): array
    {
        $created = collect();
        $conflicts = collect();

        foreach ($assignments as $assignment) {
            $placed = false;

            foreach ($slots as $slot) {
                $entry = new TimetableEntry([
                    'timetable_id' => $timetable->id,
                    'course_id' => $assignment->course_id,
                    'teacher_id' => $assignment->teacher_id,
                    'section_id' => $assignment->section_id,
                    'room_id' => $assignment->room_id,
                    'time_slot_id' => $slot->id,
                    'session_number' => $assignment->session_number ?? 1,
                ]);
                $entry->setRelation('timetable', $timetable);
                $entry->load(['teacher', 'section', 'room', 'timeSlot']);

                if ($this->conflictDetector->detect($entry)->isEmpty()) {
                    $entry->save();
                    $created->push($entry);
                    $placed = true;
                    break;
                }
            }

            if (! $placed) {
                $conflicts->push([
                    'course_id' => $assignment->course_id,
                    'teacher_id' => $assignment->teacher_id,
                    'section_id' => $assignment->section_id,
                    'message' => 'No conflict-free time slot was found.',
                ]);
            }
        }

        return ['created' => $created, 'unplaced' => $conflicts];
    }
}
