<?php

declare(strict_types=1);

namespace App\Domain\Scheduling;

use App\Models\Timetable;
use App\Models\TimetableEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class TimetableGenerator
{
    public function __construct(private readonly ConflictDetector $conflictDetector) {}

    /**
     * Generate a deterministic first-fit draft. Hard conflicts are never persisted.
     */
    public function generate(Timetable $timetable, Collection $assignments, Collection $slots): array
    {
        $created = collect();
        $unplaced = collect();

        foreach (GenerationPlan::expandSessions($assignments) as $assignment) {
            $placed = false;

            foreach ($slots as $slot) {
                if ($slot->is_break || $this->slotDuration($slot) < $assignment->duration_minutes) {
                    continue;
                }

                $entry = new TimetableEntry([
                    'timetable_id' => $timetable->id,
                    'course_id' => $assignment->course_id,
                    'teacher_id' => $assignment->teacher_id,
                    'section_id' => $assignment->section_id,
                    'room_id' => $assignment->room_id,
                    'time_slot_id' => $slot->id,
                    'session_number' => $assignment->session_number,
                ]);
                $entry->setRelation('timetable', $timetable);
                $entry->setRelation('timeSlot', $slot);
                $entry->load(['teacher', 'section', 'room']);

                if ($this->conflictDetector->detect($entry)->isEmpty()) {
                    $entry->save();
                    $created->push($entry);
                    $placed = true;
                    break;
                }
            }

            if (! $placed) {
                $unplaced->push([
                    'course_id' => $assignment->course_id,
                    'session_number' => $assignment->session_number,
                    'teacher_id' => $assignment->teacher_id,
                    'message' => 'No conflict-free time slot with sufficient duration was found.',
                ]);
            }
        }

        return ['created' => $created, 'unplaced' => $unplaced];
    }

    private function slotDuration(object $slot): int
    {
        return (int) Carbon::parse($slot->starts_at)->diffInMinutes(Carbon::parse($slot->ends_at));
    }
}
