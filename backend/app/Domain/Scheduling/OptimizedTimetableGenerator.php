<?php

declare(strict_types=1);

namespace App\Domain\Scheduling;

use App\Models\Timetable;
use App\Models\TimetableEntry;
use Illuminate\Support\Collection;

final class OptimizedTimetableGenerator
{
    public function __construct(
        private readonly ConflictDetector $conflictDetector,
        private readonly AvailabilityConstraint $availability,
        private readonly ScheduleScorer $scorer,
    ) {}

    public function generate(Timetable $timetable, Collection $assignments, Collection $slots): array
    {
        $unplaced = collect();
        $expandedAssignments = GenerationPlan::expandSessions($assignments)
            ->sortByDesc(fn ($assignment) => $assignment->session_number ?? 1)
            ->values();

        foreach ($expandedAssignments as $assignment) {
            $placed = false;

            foreach ($slots as $slot) {
                if ($slot->is_break
                    || ! $this->availability->teacherAvailable($assignment->teacher_id, $slot)
                    || ! $this->availability->roomAvailable($assignment->room_id, $slot)) {
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
                    $placed = true;
                    break;
                }
            }

            if (! $placed) {
                $unplaced->push([
                    'course_id' => $assignment->course_id,
                    'session_number' => $assignment->session_number,
                    'message' => 'No valid slot satisfies the hard constraints.',
                ]);
            }
        }

        $entries = TimetableEntry::query()
            ->where('timetable_id', $timetable->id)
            ->with('timeSlot')
            ->get();

        return [
            'entries' => $entries,
            'unplaced' => $unplaced,
            'score' => $this->scorer->score($entries, $unplaced),
        ];
    }
}
