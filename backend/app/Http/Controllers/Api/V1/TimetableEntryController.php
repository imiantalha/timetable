<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Scheduling\ConflictDetector;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimetableEntryRequest;
use App\Models\TimetableEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class TimetableEntryController extends Controller
{
    public function __construct(private readonly ConflictDetector $detector) {}

    public function index(Request $request): JsonResponse
    {
        $entries = TimetableEntry::query()
            ->where('timetable_id', $request->integer('timetable_id'))
            ->with(['course:id,name,code', 'teacher:id,first_name,last_name', 'section:id,name,code', 'room:id,name,code', 'timeSlot:id,day_of_week,starts_at,ends_at'])
            ->orderBy('time_slot_id')
            ->paginate($request->integer('per_page', 50));

        return response()->json(['data' => $entries]);
    }

    public function store(StoreTimetableEntryRequest $request): JsonResponse
    {
        $entry = TimetableEntry::create($request->validated());
        $conflicts = $this->detector->detect($entry);

        if ($conflicts->isNotEmpty()) {
            $entry->delete();
            throw ValidationException::withMessages(['schedule' => $conflicts->all()]);
        }

        return response()->json(['data' => $entry->load(['course','teacher','section','room','timeSlot'])], 201);
    }

    public function show(TimetableEntry $timetableEntry): JsonResponse
    {
        return response()->json(['data' => $timetableEntry->load(['course','teacher','section','room','timeSlot'])]);
    }

    public function destroy(TimetableEntry $timetableEntry): JsonResponse
    {
        $timetableEntry->delete();
        return response()->json(null, 204);
    }
}
