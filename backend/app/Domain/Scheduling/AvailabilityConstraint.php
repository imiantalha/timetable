<?php

declare(strict_types=1);

namespace App\Domain\Scheduling;

use App\Models\RoomAvailability;
use App\Models\TeacherAvailability;
use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Builder;

final class AvailabilityConstraint
{
    public function teacherAvailable(int $teacherId, TimeSlot $slot): bool
    {
        return $this->isAvailable(
            TeacherAvailability::query()->where('teacher_id', $teacherId),
            $slot,
        );
    }

    public function roomAvailable(int $roomId, TimeSlot $slot): bool
    {
        return $this->isAvailable(
            RoomAvailability::query()->where('room_id', $roomId),
            $slot,
        );
    }

    private function isAvailable(Builder $availability, TimeSlot $slot): bool
    {
        // No configured windows means the resource has no restrictions.
        if (! (clone $availability)->exists()) {
            return true;
        }

        $forDay = fn (): Builder => (clone $availability)
            ->where('day_of_week', $slot->day_of_week);

        // An explicit unavailable interval takes precedence, including partial
        // overlaps with a slot that is otherwise covered by an available window.
        $hasUnavailableOverlap = $forDay()
            ->where('is_available', false)
            ->where('starts_at', '<', $slot->ends_at)
            ->where('ends_at', '>', $slot->starts_at)
            ->exists();

        if ($hasUnavailableOverlap) {
            return false;
        }

        // When availability is configured, require one available window to
        // cover the entire selected slot.
        return $forDay()
            ->where('is_available', true)
            ->where('starts_at', '<=', $slot->starts_at)
            ->where('ends_at', '>=', $slot->ends_at)
            ->exists();
    }
}
