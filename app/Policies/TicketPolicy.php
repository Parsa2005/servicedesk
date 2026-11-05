<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /**
     * بررسی دسترسی سراسری برای کریتور (مالک سیستم)
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->level === 'creator') {
            return true;
        }
        
        return null;
    }

    /**
     * مشاهده لیست تیکت‌ها
     */
    public function viewAny(User $user): bool
    {
        // همه کاربران غیر از سطح user عادی اجازه ورود به پنل و دیدن لیست را دارند (بر اساس پرمیشن‌ها)
        return $user->level !== 'user';
    }

    /**
     * مشاهده یک تیکت خاص
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // اگر کاربر کارمند (Employee) است، فقط تیکت‌های خودش را می‌بیند
        if ($user->level === 'employee') {
            return $ticket->user_id === $user->id;
        }

        // اگر کاربر ادمین یا کارشناس شرکت است، تیکت باید متعلق به شرکت خودش باشد
        return $ticket->company_id === $user->company_id;
    }

    /**
     * ایجاد تیکت جدید
     */
    public function create(User $user): bool
    {
        // تمام کاربران سازمان که شرکت دارند می‌توانند تیکت ثبت کنند
        return $user->company_id !== null;
    }

    /**
     * ویرایش یا پاسخ به تیکت
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // تیکت باید حتماً متعلق به شرکت کاربر باشد
        return $ticket->company_id === $user->company_id;
    }
}