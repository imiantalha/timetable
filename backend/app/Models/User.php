<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['institution_id', 'name', 'email', 'password', 'role', 'is_active'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean'];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }

    public function hasRole(string $role): bool { return $this->role === $role; }
    public function isAdmin(): bool { return $this->hasRole('admin'); }
    public function isTeacher(): bool { return $this->hasRole('teacher'); }
    public function isStudent(): bool { return $this->hasRole('student'); }
}
