<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class PanelTicketPolicy
{
    protected function canAccessTicket(User $user, Ticket $ticket): bool
    {
        if ($user->level === 'creator') {
            return true;
        }

        $isManager = $user->roles()->where('slug', 'company-manager')->exists();
        if ($isManager && $user->company) {
            $allowedIds = $user->company->getDescendantCompanyIds();
            return in_array((int) $ticket->company_id, $allowedIds, true)
                || in_array((int) $ticket->target_company_id, $allowedIds, true);
        }

        return (int) $ticket->company_id === (int) $user->company_id
            || (int) $ticket->target_company_id === (int) $user->company_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->level === 'creator'
            || $user->roles()->where('slug', 'company-manager')->exists()
            || $user->hasPermission('tickets.index')
            || $user->hasPermission('tickets.view')
            || $user->hasPermission('tickets.manage');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->level === 'creator') {
            return true;
        }

        if (!$this->canAccessTicket($user, $ticket)) {
            return false;
        }

        $isAuthor = ($ticket->user_id === $user->id);
        $isManager = $user->roles()->where('slug', 'company-manager')->exists();

        if ($isAuthor || $isManager) {
            return true;
        }

        if ($user->department_id && $ticket->department_id !== $user->department_id && !$user->hasPermission('tickets.view-all')) {
            return false;
        }

        return true;
    }

    public function reply(User $user, Ticket $ticket): bool
    {
        if ($user->level === 'creator') {
            return true;
        }

        if (!$this->canAccessTicket($user, $ticket)) {
            return false;
        }

        $isAuthor = ($ticket->user_id === $user->id);
        $isManager = $user->roles()->where('slug', 'company-manager')->exists();

        return $isAuthor || $isManager || $user->hasPermission('tickets.reply');
    }

    public function manage(User $user, Ticket $ticket): bool
    {
        return $this->canAccessTicket($user, $ticket);
    }

    public function close(User $user, Ticket $ticket): bool
    {
        if (!$this->manage($user, $ticket)) {
            return false;
        }

        $isAuthor = ($ticket->user_id === $user->id);
        $isManager = $user->roles()->where('slug', 'company-manager')->exists();
        $hasManagePermission = ($user->level === 'creator'
            || $isManager
            || $user->hasPermission('tickets.manage')
            || $user->hasPermission('tickets.close')
            || $ticket->department_id === $user->department_id);

        return $isAuthor || $hasManagePermission;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        if (!$this->manage($user, $ticket)) {
            return false;
        }

        $isManager = $user->roles()->where('slug', 'company-manager')->exists();

        return $user->level === 'creator'
            || $isManager
            || $user->hasPermission('tickets.delete')
            || $user->hasPermission('tickets.manage');
    }
}