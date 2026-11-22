<?php

declare(strict_types=1);

namespace App\\Http\\Controllers\\Api\\V1;

use App\\Domain\\Scheduling\\AvailabilityConstraint;
use App\\Domain\\Scheduling\\ConflictDetector;
use App\\Http\\Controllers\\Controller;
use App\\Http\\Requests\\StoreTimetableEntryRequest;
use App\\Models\\Course;
use App\\Models\\Room;
use App\\Models\\Section;
use App\\Models\\Teacher;
use App\\Models\\TimeSlot;
use App\\Models\\Timetable;
use App\\Models\\TimetableEntry;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Carbon;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Validation\\ValidationException;
use Symfony\\Component\\HttpKernel\\Exception\\ConflictHttpException;

final class TimetableEntryController extends Controller
{
    public function __construct(
        private readonly ConflictDetector $detector,
        private readonly AvailabilityConstraint $availability,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $entries = TimetableEntry::query()
            ->whereHas('timetable', fn ($query) => $query->when($request->user()?->institution_id, fn ($builder, $id) => $builder->where('institution_id', $id)))
            ->where('timetable_id', $request->integer('timetable_id'))
            ->with(['course:id,name,code', 'teacher:id,first_name,last_name', 'section:id,name,code', 'room:id,name,code', 'timeSlot:id,day_of_week,starts_at,ends_at'])
            ->orderBy('time_slot_id')
            ->paginate($request->integer('per_page', 50));

        return response()->json(['data' => $entries]);
    }

    public function store(StoreTimetableEntryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $entry = DB::transaction(function () use ($data): TimetableEntry {
            $timetable = Timetable::query()->whereKey($data['timetable_id'])->lockForUpdate()->firstOrFail();

            if (auth()->user()?->institution_id !== null
                && (int) auth()->user()->institution_id !== (int) $timetable->institution_id) {
                abort(404);
            }

            if ($timetable->status !== 'draft') {
                throw new ConflictHttpException('Entries can only be changed while the timetable is a draft.');
            }

            $course = Course::with('department')->findOrFail($data['course_id']);
            $section = Section::with('department')->findOrFail($data['section_id']);
            $teacher = Teacher::findOrFail($data['teacher_id']);
            $room = Room::findOrFail($data['room_id']);
            $slot = TimeSlot::findOrFail($data['time_slot_id']);

            $sameInstitution = (int) $teacher->institution_id === (int) $timetable->institution_id
                && (int) $room->institution_id === (int) $timetable->institution_id
                && (int) $slot->institution_id === (int) $timetable->institution_id
                && (int) $course->department->institution_id === (int) $timetable->institution_id
                && (int) $section->department->institution_id === (int) $timetable->institution_id;

            if (! $sameInstitution || (int) $course->department_id !== (int) $section->department_id || $slot->is_break) {
                throw ValidationException::withMessages([
                    'schedule' => ['The selected course, section, teacher, room and time slot must be compatible with this timetable.'],
                ]);
            }

            $slotDuration = Carbon::parse($slot->starts_at)->diffInMinutes(Carbon::parse($slot->ends_at));

            if ($slotDuration < (int) $course->duration_minutes) {
                throw ValidationException::withMessages([
                    'time_slot_id' => ['The selected time slot is shorter than the course duration.'],
                ]);
            }

            $duplicate = TimetableEntry::query()
                ->where('timetable_id', $timetable->id)
                ->where('course_id', $course->id)
                ->where('section_id', $section->id)
                ->where('session_number', $data['session_number'])
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'session_number' => ['This course session already exists for the selected section in the timetable.'],
                ]);
            }

            if (! $this->availability->teacherAvailable($teacher->id, $slot)
                || ! $this->availability->roomAvailable($room->id, $slot)) {
                throw ValidationException::withMessages([
                    'schedule' => ['The selected teacher or room is unavailable during this time slot.'],
                ]);
            }

            $entry = TimetableEntry::create($data);
            $conflicts = $this->detector->detect($entry);

            if ($conflicts->isNotEmpty()) {
                throw ValidationException::withMessages(['schedule' => $conflicts->all()]);
            }

            return $entry->load(['course','teacher','section','room','timeSlot']);
        });

        return response()->json(['data' => $entry], 201);
    }

    public function show(TimetableEntry $timetableEntry): JsonResponse
    {
        return response()->json(['data' => $timetableEntry->load(['course','teacher','section','room','timeSlot'])]);
    }

    public function destroy(TimetableEntry $timetableEntry): JsonResponse
    {
        abort_unless(request()->user()?->role === 'admin', 403);
        abort_unless($timetableEntry->timetable?->status === 'draft', 409, 'Published timetable entries cannot be deleted.');
        $timetableEntry->delete();

        return response()->json(null, 204);
    }
}
