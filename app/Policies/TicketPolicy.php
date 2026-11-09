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
        return $this->view($user, $ticket);
    }

    public function close(User $user, Ticket $ticket): bool
    {
        return $ticket->user_id === $user->id || $ticket->target_company_id === $user->company_id;
    }
}