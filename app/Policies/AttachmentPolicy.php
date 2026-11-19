<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    public function download(User $user, Attachment $attachment): bool
    {
        $ticket = $attachment->ticket ?? optional($attachment->comment)->ticket;

        if (!$ticket) {
            return false;
        }

        if ($user->isCreator() || $user->hasPermission('tickets.view-all') || $user->hasPermission('attachments.view')) {
            
            return true;
        }

        return $ticket->user_id === $user->id || $ticket->assigned_to === $user->id;
    }
}