<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Scheduling\ConflictDetector;
use App\Http\Controllers\Controller;
use App\Http\Requests\MoveTimetableEntryRequest;
use App\Models\TimeSlot;
use App\Models\TimetableEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class TimetableEntryMoveController extends Controller
{
    public function __construct(private readonly ConflictDetector $detector) {}

    public function __invoke(MoveTimetableEntryRequest $request, TimetableEntry $timetableEntry): JsonResponse
    {
        $slot = TimeSlot::findOrFail($request->integer('time_slot_id'));

        return DB::transaction(function () use ($timetableEntry, $slot): JsonResponse {
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
