<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;


class Ticket extends Model // یا همان Ticket شما
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'sla_due_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->tracking_code)) {
                // تولید یک کد پیگیری یکتا (مثلا: TICK-A1B2C3D4)
                $ticket->tracking_code = 'TICK-' . strtoupper(Str::random(8));
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class);
    }

    // استفاده از کامنت‌ها برای پاسخ‌ها و یادداشت‌های داخلی
    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'open'        => 'باز',
            'pending'     => 'در انتظار بررسی',
            'in_progress' => 'در حال بررسی',
            'resolved'    => 'حل شده',
            'closed'      => 'بسته شده',
            default       => 'نامشخص',
        };
    }

    /**
     * کلاس رنگی (Bootstrap Badge) مربوط به هر وضعیت
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'open'        => 'bg-secondary',
            'pending'     => 'bg-warning text-dark',
            'in_progress' => 'bg-info text-dark',
            'resolved'    => 'bg-success',
            'closed'      => 'bg-dark',
            default       => 'bg-secondary',
        };
    }
}