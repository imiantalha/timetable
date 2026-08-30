<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;
    protected $fillable = ['institution_id', 'name', 'code', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function courses(): HasMany { return $this->hasMany(Course::class); }
    public function sections(): HasMany { return $this->hasMany(Section::class); }
}
