<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Scheduling\AvailabilityConstraint;
use App\Domain\Scheduling\ConflictDetector;
use App\Domain\Scheduling\OptimizedTimetableGenerator;
use App\Domain\Scheduling\ScheduleScorer;
use App\Models\Timetable;
use App\Models\TimeSlot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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
        $timetable = Timetable::findOrFail($this->timetableId);
        $slots = TimeSlot::query()->whereIn('id', $this->timeSlotIds)->orderBy('day_of_week')->orderBy('starts_at')->get();
        $result = $generator->generate($timetable, collect($this->assignments)->map(fn (array $item) => (object) $item), $slots);
        Cache::put("timetable-generation:{$this->generationId}", ['status' => 'completed', 'created_count' => $result['entries']->count(), 'unplaced_count' => $result['unplaced']->count(), 'score' => $result['score']], now()->addHour());
    }

    public function failed(\Throwable $exception): void
    {
        Cache::put("timetable-generation:{$this->generationId}", ['status' => 'failed', 'message' => $exception->getMessage()], now()->addHour());
    }
}
