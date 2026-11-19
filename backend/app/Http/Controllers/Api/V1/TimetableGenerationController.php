<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Scheduling\TimetableGenerator;
use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateTimetableRequest;
use App\Models\Timetable;
use App\Models\TimeSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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
