<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    public function download(User $user, Attachment$attachment): bool
    {
        $ticket = $attachment->ticket ?? optional($attachment->comment)->ticket;

        if (!$ticket) {
            return false;
        }

        if ($user->isCreator()) {
            return true;
        }

        if ($ticket->user_id ===$user->id || $ticket->assigned_to ===$user->id) {
            return true;
        }

        $isManager =$user->roles()->where('slug', 'company-manager')->exists();
        $hasPermission =$isManager
            || $user->hasPermission('tickets.view-all')
            || $user->hasPermission('attachments.view')
            || $user->hasPermission('tickets.attachments.view');

        if (!$hasPermission) {
            return false;
        }

        if ($isManager &&$user->company) {
            $allowedIds =$user->company->getDescendantCompanyIds();
            return in_array((int) $ticket->company_id,$allowedIds, true)
                || in_array((int) $ticket->target_company_id,$allowedIds, true);
        }

        return (int) $user->company_id === (int)$ticket->company_id
            || (int) $user->company_id === (int)$ticket->target_company_id;
    }
}