<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;
    protected $fillable = ['institution_id', 'section_id', 'student_number', 'first_name', 'last_name', 'email', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
}
