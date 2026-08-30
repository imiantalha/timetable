<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;
    protected $fillable = ['institution_id', 'name', 'code', 'capacity', 'type', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean', 'capacity' => 'integer']; }
    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function availabilities(): HasMany { return $this->hasMany(RoomAvailability::class); }
    public function timetableEntries(): HasMany { return $this->hasMany(TimetableEntry::class); }
}
