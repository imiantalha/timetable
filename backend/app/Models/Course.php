<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;
    protected $fillable = ['department_id', 'name', 'code', 'credits', 'sessions_per_week', 'duration_minutes', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean', 'credits' => 'integer', 'sessions_per_week' => 'integer', 'duration_minutes' => 'integer']; }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function timetableEntries(): HasMany { return $this->hasMany(TimetableEntry::class); }
}
