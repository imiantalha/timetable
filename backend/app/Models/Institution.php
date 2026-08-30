<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'timezone', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function departments(): HasMany { return $this->hasMany(Department::class); }
    public function teachers(): HasMany { return $this->hasMany(Teacher::class); }
    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function rooms(): HasMany { return $this->hasMany(Room::class); }
}
