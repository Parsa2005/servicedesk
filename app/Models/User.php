<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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

    /**
     * بررسی مجوزهای کاربر بر اساس سطح و نقش‌ها
     */
    public function hasPermission(string $slug): bool
    {
        // اگر کاربر سطحش عادی (user) بود، کلاً هیچ دسترسی ادمینی ندارد
        if ($this->level === 'user') {
            return false;
        }

        // اگر کاربر سازنده/ادمین کل بود، تمام دسترسی‌ها را دارد
        if ($this->level === 'creator') {
            return true;
        }

        // بررسی اینکه آیا نقش‌های کاربر شامل این مجوز (slug) هستند یا خیر
        return $this->roles()->whereHas('permissions', function ($query) use ($slug) {
            $query->where('slug', $slug);
        })->exists();
    }


}