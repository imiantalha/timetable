<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Scheduling\TimetableGenerator;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateTimetableRequest;
use App\Models\Timetable;
use App\Models\Course;
use App\Models\Room;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\TimeSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class TimetableGenerationController extends Controller
{
    public function __construct(private readonly TimetableGenerator $generator) {}

    public function generate(GenerateTimetableRequest $request, Timetable $timetable): JsonResponse
    {
        $payload = $request->validated();
        $assignments = collect($payload['assignments'])->map(fn (array $item) => (object) $item);

        $result = DB::transaction(function () use ($timetable, $payload, $assignments): array {
            $lockedTimetable = Timetable::query()->whereKey($timetable->id)->lockForUpdate()->firstOrFail();

            if ($lockedTimetable->status !== 'draft') {
                throw new ConflictHttpException('Only draft timetables can be generated.');
            }

            if ($lockedTimetable->entries()->exists()) {
                throw new ConflictHttpException('This timetable already contains entries. Clear the draft before generating it again.');
            }

            $courseIds = $assignments->pluck('course_id')->unique();
            $sectionIds = $assignments->pluck('section_id')->unique();
            $teacherIds = $assignments->pluck('teacher_id')->unique();
            $roomIds = $assignments->pluck('room_id')->unique();

            // Scope indirect resources through their department's institution.
            // Existence validation alone would allow a tenant admin to attach
            // another institution's course or section to their own timetable.
            $courses = Course::query()
                ->whereIn('id', $courseIds)
                ->whereHas('department', fn ($query) => $query->where('institution_id', $lockedTimetable->institution_id))
                ->get(['id', 'department_id'])
                ->keyBy('id');

            $sections = Section::query()
                ->whereIn('id', $sectionIds)
                ->whereHas('department', fn ($query) => $query->where('institution_id', $lockedTimetable->institution_id))
                ->get(['id', 'department_id'])
                ->keyBy('id');

            $validTeachers = Teacher::query()
                ->where('institution_id', $lockedTimetable->institution_id)
                ->whereIn('id', $teacherIds)
                ->count();

            $validRooms = Room::query()
                ->where('institution_id', $lockedTimetable->institution_id)
                ->whereIn('id', $roomIds)
                ->count();

            if ($courses->count() !== $courseIds->count()
                || $sections->count() !== $sectionIds->count()
                || $validTeachers !== $teacherIds->count()
                || $validRooms !== $roomIds->count()) {
                throw ValidationException::withMessages([
                    'assignments' => ['Every selected resource must belong to the timetable institution.'],
                ]);
            }

            foreach ($assignments as $assignment) {
                if ((int) $courses[$assignment->course_id]->department_id !== (int) $sections[$assignment->section_id]->department_id) {
                    throw ValidationException::withMessages([
                        'assignments' => ['Each course and section must belong to the same department.'],
                    ]);
                }
            }

            $slots = TimeSlot::query()
                ->where('institution_id', $lockedTimetable->institution_id)
                ->whereIn('id', $payload['time_slot_ids'])
                ->where('is_break', false)
                ->orderBy('day_of_week')
                ->orderBy('starts_at')
                ->get();

            if ($slots->count() !== count(array_unique($payload['time_slot_ids']))) {
                throw new ConflictHttpException('One or more selected time slots do not belong to this institution or are unavailable.');
            }

            return $this->generator->generate($lockedTimetable, $assignments, $slots);
        });

        return response()->json([
            'data' => [
                'timetable_id' => $timetable->id,
                'created_count' => $result['created']->count(),
                'unplaced_count' => $result['unplaced']->count(),
                'unplaced' => $result['unplaced']->values(),
            ],
        ]);
    }
}
