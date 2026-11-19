<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Scheduling\OptimizedTimetableGenerator;
use App\Models\Timetable;
use App\Models\TimeSlot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class GenerateTimetableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $timetableId,
        public readonly array $assignments,
        public readonly array $timeSlotIds,
        public readonly string $generationId,
    ) {}

    public function handle(OptimizedTimetableGenerator $generator): void
    {
        Cache::put("timetable-generation:{$this->generationId}", ['status' => 'running'], now()->addHour());

        $result = DB::transaction(function () use ($generator): array {
            $timetable = Timetable::query()->whereKey($this->timetableId)->lockForUpdate()->firstOrFail();

            if ($timetable->status !== 'draft') {
                throw new \\Symfony\\Component\\HttpKernel\\Exception\\ConflictHttpException('Only draft timetables can be generated.');
            }

            if ($timetable->entries()->exists()) {
                throw new \\Symfony\\Component\\HttpKernel\\Exception\\ConflictHttpException('This timetable already contains entries. Clear the draft before generating it again.');
            }

            $slots = TimeSlot::query()
                ->where('institution_id', $timetable->institution_id)
                ->whereIn('id', $this->timeSlotIds)
                ->where('is_break', false)
                ->orderBy('day_of_week')
                ->orderBy('starts_at')
                ->get();

            return $generator->generate(
                $timetable,
                collect($this->assignments)->map(fn (array $item) => (object) $item),
                $slots,
            );
        });

        Cache::put("timetable-generation:{$this->generationId}", [
            'status' => 'completed',
            'created_count' => $result['created_count'],
            'unplaced_count' => $result['unplaced']->count(),
            'score' => $result['score'],
        ], now()->addHour());
    }

    public function failed(\Throwable $exception): void
    {
        Cache::put("timetable-generation:{$this->generationId}", [
            'status' => 'failed',
            'message' => $exception->getMessage(),
        ], now()->addHour());
    }
}
