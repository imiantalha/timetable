<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timetable extends Model
{
    use HasFactory;
    protected $fillable = ['institution_id', 'semester_id', 'name', 'status', 'published_at'];
    protected function casts(): array { return ['published_at' => 'datetime']; }
    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function entries(): HasMany { return $this->hasMany(TimetableEntry::class); }
}
