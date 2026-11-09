<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    // مشاهده لیست تیکت‌ها
    public function viewAny(User $user): bool
    {
        return true; 
    }

    // مشاهده جزئیات یک تیکت خاص
    public function view(User $user, Ticket $ticket): bool
    {
        if ($ticket->user_id === $user->id) {
            return true;
        }

        if ($ticket->assigned_to === $user->id) {
            return true;
        }

        if (empty($user->company_id)) {
            return false;
        }

        $isRelatedCompany = ($user->company_id === $ticket->company_id || $user->company_id === $ticket->target_company_id);

        if (!$isRelatedCompany) {
            return false;
        }

        return true;
    }

    // ایجاد تیکت جدید
    public function create(User $user): bool
    {
        return true;
    }

    // 👈 بررسی دسترسی ویرایش تیکت
    public function update(User $user, Ticket $ticket): bool
    {
        // کاربر باید یا ایجادکننده تیکت باشد یا شخص مسئول فعلی
        if ($ticket->user_id !== $user->id && $ticket->assigned_to !== $user->id) {
            return false;
        }

        // بررسی اینکه آیا طرف مقابل پاسخی داده است یا خیر
        return $ticket->canBeEditedBy($user);
    }

    // پاسخ دادن به تیکت
    public function reply(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    // بستن تیکت
    public function close(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id || $ticket->target_company_id === $user->company_id;
    }
}