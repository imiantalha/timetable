<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Scheduling\TimetableGenerator;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateTimetableRequest;
use App\Models\Timetable;
use App\Models\TimeSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class TimetableGenerationController extends Controller
{
    public function __construct(private readonly TimetableGenerator $generator) {}

    public function generate(GenerateTimetableRequest $request, Timetable $timetable): JsonResponse
    {
        $payload = $request->validated();
        $assignments = collect($payload['assignments'])->map(fn (array $item) => (object) $item);
        $slots = TimeSlot::query()->whereIn('id', $payload['time_slot_ids'])->orderBy('day_of_week')->orderBy('starts_at')->get();

        $result = DB::transaction(fn () => $this->generator->generate($timetable, $assignments, $slots));

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
