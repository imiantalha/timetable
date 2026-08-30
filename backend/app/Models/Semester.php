<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id', 'name', 'starts_on', 'ends_on', 'is_active'];
    protected function casts(): array { return ['starts_on' => 'date', 'ends_on' => 'date', 'is_active' => 'boolean']; }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function timetables(): HasMany { return $this->hasMany(Timetable::class); }
}
