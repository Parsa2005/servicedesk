<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    
    public function viewAny(User $user): bool
    {
        return true; 
    }

    /**
     * بررسی مجوز مشاهده یک تیکت خاص
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // اگر ادمین کل (کریتور) است یا پرمیشن مشاهده همه را دارد
        if ($user->isCreator() || $user->hasPermission('tickets.view-all')) {
            return true;
        }

        // اگر کاربر نویسنده تیکت یا پشتیبان مسئول است
        if ($ticket->user_id === $user->id || $ticket->assigned_to === $user->id) {
            return true;
        }

        if (empty($user->company_id)) {
            return false;
        }

        // بررسی اینکه تیکت مربوط به شرکت کاربر یا شرکت مقصد است
        $isRelatedCompany = ($user->company_id === $ticket->company_id || $user->company_id === $ticket->target_company_id);

        return $isRelatedCompany;
    }

    /**
     * اجازه ایجاد تیکت جدید
     */
    public function create(User $user): bool
    {
        return true; 
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($ticket->user_id !== $user->id && $ticket->assigned_to !== $user->id) {
            return false;
        }

        return $ticket->canBeEditedBy($user);
    }

    
    public function reply(User $user, Ticket $ticket): bool
    {
        if (!$this->view($user, $ticket)) {
            return false;
        }

        if ($ticket->user_id === $user->id || $ticket->assigned_to === $user->id) {
            return true;
        }

        return $user->isCreator() || $user->hasPermission('tickets.reply');
    }

    
    public function close(User $user, Ticket $ticket): bool
    {
        if ($ticket->user_id === $user->id) {
            return true;
        }

        $hasManagePermission = ($user->isCreator() || $user->hasPermission('tickets.manage') || $user->hasPermission('tickets.close'));

        return ($ticket->target_company_id === $user->company_id) && $hasManagePermission;
    }
}