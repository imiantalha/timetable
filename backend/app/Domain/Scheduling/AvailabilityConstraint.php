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
        return TeacherAvailability::query()
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $slot->day_of_week)
            ->where('is_available', true)
            ->where('starts_at', '<=', $slot->starts_at)
            ->where('ends_at', '>=', $slot->ends_at)
            ->exists();
    }

    public function roomAvailable(int $roomId, TimeSlot $slot): bool
    {
        return RoomAvailability::query()
            ->where('room_id', $roomId)
            ->where('day_of_week', $slot->day_of_week)
            ->where('is_available', true)
            ->where('starts_at', '<=', $slot->starts_at)
            ->where('ends_at', '>=', $slot->ends_at)
            ->exists();
    }
}
