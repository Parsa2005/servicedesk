<?php

namespace App\Policies;

class PanelTicketPolicy
{
    public function viewAny(\App\Models\User $user): bool
    {
        return $user->level === 'creator' || 
               $user->hasPermission('tickets.index') || 
               $user->hasPermission('tickets.view') || 
               $user->hasPermission('tickets.manage');
    }

    public function view(\App\Models\User $user, \App\Models\Ticket $ticket): bool
    {
        if ($user->level !== 'creator' && $ticket->company_id !== $user->company_id) {
            return false;
        }

        $isAuthor = ($ticket->user_id === $user->id);

        if ($isAuthor || $user->level === 'creator') {
            return true;
        }

        if ($user->department_id && $ticket->department_id !== $user->department_id && !$user->hasPermission('tickets.view-all')) {
            return false;
        }

        return true;
    }

    public function reply(\App\Models\User $user, \App\Models\Ticket $ticket): bool
    {
        if ($user->level !== 'creator' && $ticket->company_id !== $user->company_id) {
            return false;
        }

        $isAuthor = ($ticket->user_id === $user->id);
        $hasReplyPermission = ($user->level === 'creator' || $user->hasPermission('tickets.reply'));

        return $isAuthor || $hasReplyPermission;
    }

    public function manage(\App\Models\User $user, \App\Models\Ticket $ticket): bool
    {
        return $user->level === 'creator' || $ticket->company_id === $user->company_id;
    }

    public function close(\App\Models\User $user, \App\Models\Ticket $ticket): bool
    {
        if (!$this->manage($user, $ticket)) {
            return false;
        }

        $isAuthor = ($ticket->user_id === $user->id);
        $hasManagePermission = ($user->level === 'creator' || 
                                $user->hasPermission('tickets.manage') || 
                                $user->hasPermission('tickets.close') || 
                                $ticket->department_id === $user->department_id);

        return $isAuthor || $hasManagePermission;
    }

    public function delete(\App\Models\User $user, \App\Models\Ticket $ticket): bool
    {
        if (!$this->manage($user, $ticket)) {
            return false;
        }

        return $user->level === 'creator' || 
               $user->hasPermission('tickets.delete') || 
               $user->hasPermission('tickets.manage');
    }
}