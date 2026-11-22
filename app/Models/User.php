<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [
        'id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function isCreator(): bool
    {
        return $this->level === 'creator';
    }
    
    public function hasPermission(string $slug): bool
    {
        if ($this->level === 'user') {
            return false;
        }
        
        if ($this->isCreator()) {
            return true;
        }
        
        return $this->roles()->whereHas('permissions', function ($query) use ($slug) {
            $query->where('slug', $slug);
        })->exists();
    }
}