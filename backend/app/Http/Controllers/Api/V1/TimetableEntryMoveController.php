<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Scheduling\AvailabilityConstraint;
use App\Domain\Scheduling\ConflictDetector;
use App\Http\Controllers\Controller;
use App\Http\Requests\MoveTimetableEntryRequest;
use App\Models\TimeSlot;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class TimetableEntryMoveController extends Controller
{
    public function __construct(
        private readonly ConflictDetector $detector,
        private readonly AvailabilityConstraint $availability,
    ) {}

    public function __invoke(MoveTimetableEntryRequest $request, TimetableEntry $timetableEntry): JsonResponse
    {
        $slot = TimeSlot::findOrFail($request->integer('time_slot_id'));
        $timetableEntry->loadMissing('course');

        return DB::transaction(function () use ($timetableEntry, $slot): JsonResponse {
            $timetable = Timetable::query()
                ->whereKey($timetableEntry->timetable_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($timetable->status !== 'draft') {
                throw new ConflictHttpException('Published timetable entries cannot be moved.');
            }

            if ((int) $slot->institution_id !== (int) $timetable->institution_id || $slot->is_break) {
                throw ValidationException::withMessages([
                    'time_slot_id' => ['Choose a non-break time slot from the same institution.'],
                ]);
            }

            $slotDuration = Carbon::parse($slot->starts_at)->diffInMinutes(Carbon::parse($slot->ends_at));

            if ($slotDuration < (int) $timetableEntry->course->duration_minutes) {
                throw ValidationException::withMessages([
                    'time_slot_id' => ['The selected time slot is shorter than the course duration.'],
                ]);
            }

            $timetableEntry->loadMissing(['teacher', 'room']);

            if (! $this->availability->teacherAvailable($timetableEntry->teacher_id, $slot)
                || ! $this->availability->roomAvailable($timetableEntry->room_id, $slot)) {
                throw ValidationException::withMessages([
                    'time_slot_id' => ['The assigned teacher or room is unavailable during this time slot.'],
                ]);
            }

            $timetableEntry->time_slot_id = $slot->id;
            $timetableEntry->setRelation('timeSlot', $slot);
            $conflicts = $this->detector->detect($timetableEntry);

            if ($conflicts->isNotEmpty()) {
                throw ValidationException::withMessages(['schedule' => $conflicts->values()->all()]);
            }

            $timetableEntry->save();

            return response()->json(['data' => $timetableEntry->fresh()->load(['course','teacher','section','room','timeSlot'])]);
        });
    }
}
