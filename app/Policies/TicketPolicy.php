<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    protected function isRelatedCompany(User $user, Ticket $ticket): bool
    {
        if ($user->isCreator()) {
            return true;
        }

        $isManager = $user->roles()->where('slug', 'company-manager')->exists();
        if ($isManager && $user->company) {
            $allowedIds = $user->company->getDescendantCompanyIds();
            return in_array((int) $ticket->company_id, $allowedIds, true)
                || in_array((int) $ticket->target_company_id, $allowedIds, true);
        }

        if (empty($user->company_id)) {
            return false;
        }

        return (int) $user->company_id === (int) $ticket->company_id
            || (int) $user->company_id === (int) $ticket->target_company_id;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isCreator() || $user->hasPermission('tickets.view-all')) {
            return true;
        }

        if ($ticket->user_id === $user->id || $ticket->assigned_to === $user->id) {
            return true;
        }

        return $this->isRelatedCompany($user, $ticket);
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
        if (!$this->view($user, $ticket)) {
            return false;
        }

        if ($ticket->user_id === $user->id || $ticket->assigned_to === $user->id) {
            return true;
        }

        return $user->isCreator()
            || $user->roles()->where('slug', 'company-manager')->exists()
            || $user->hasPermission('tickets.reply');
    }

    public function close(User $user, Ticket $ticket): bool
    {
        if ($ticket->user_id === $user->id) {
            return true;
        }

        $hasManagePermission = $user->isCreator()
            || $user->roles()->where('slug', 'company-manager')->exists()
            || $user->hasPermission('tickets.manage')
            || $user->hasPermission('tickets.close');

        return $this->isRelatedCompany($user, $ticket) && $hasManagePermission;
    }
}