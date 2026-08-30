<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableEntry extends Model
{
    use HasFactory;
    protected $fillable = ['timetable_id', 'course_id', 'teacher_id', 'section_id', 'room_id', 'time_slot_id', 'session_number'];
    protected function casts(): array { return ['session_number' => 'integer']; }
    public function timetable(): BelongsTo { return $this->belongsTo(Timetable::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function room(): BelongsTo { return $this->belongsTo(Room::class); }
    public function timeSlot(): BelongsTo { return $this->belongsTo(TimeSlot::class); }
}
