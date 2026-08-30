<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSlot extends Model
{
    use HasFactory;
    protected $fillable = ['institution_id', 'day_of_week', 'starts_at', 'ends_at', 'is_break'];
    protected function casts(): array { return ['day_of_week' => 'integer', 'is_break' => 'boolean']; }
    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function timetableEntries(): HasMany { return $this->hasMany(TimetableEntry::class); }
}
