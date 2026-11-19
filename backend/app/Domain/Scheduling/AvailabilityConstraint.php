<?php

declare(strict_types=1);

namespace App\Domain\Scheduling;

use App\Models\RoomAvailability;
use App\Models\TeacherAvailability;
use App\Models\TimeSlot;

final class AvailabilityConstraint
{
    public function teacherAvailable(int $teacherId, TimeSlot $slot): bool
    {
        $availability = TeacherAvailability::query()->where('teacher_id', $teacherId);

        if (! (clone $availability)->exists()) {
            return true;
        }

        return $availability
            ->where('day_of_week', $slot->day_of_week)
            ->where('is_available', true)
            ->where('starts_at', '<=', $slot->starts_at)
            ->where('ends_at', '>=', $slot->ends_at)
            ->exists();
    }

    public function roomAvailable(int $roomId, TimeSlot $slot): bool
    {
        $availability = RoomAvailability::query()->where('room_id', $roomId);

        if (! (clone $availability)->exists()) {
            return true;
        }

        return $availability
            ->where('day_of_week', $slot->day_of_week)
            ->where('is_available', true)
            ->where('starts_at', '<=', $slot->starts_at)
            ->where('ends_at', '>=', $slot->ends_at)
            ->exists();
    }
}
