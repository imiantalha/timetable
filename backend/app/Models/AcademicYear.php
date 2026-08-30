<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;
    protected $fillable = ['institution_id', 'name', 'starts_on', 'ends_on', 'is_active'];
    protected function casts(): array { return ['starts_on' => 'date', 'ends_on' => 'date', 'is_active' => 'boolean']; }
    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function semesters(): HasMany { return $this->hasMany(Semester::class); }
}
