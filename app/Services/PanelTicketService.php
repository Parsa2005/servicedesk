<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class PanelTicketService
{
    protected function isCompanyManager(User $user): bool
    {
        return $user->roles()->where('slug', 'company-manager')->exists();
    }

    protected function allowedCompanyIds(User $user): ?array
    {
        if ($user->level === 'creator' || (method_exists($user, 'isCreator') && $user->isCreator())) {
            return null;
        }

        if ($this->isCompanyManager($user) && $user->company) {
            return $user->company->getDescendantCompanyIds();
        }

        return $user->company_id ? [(int) $user->company_id] : [];
    }

    public function getFilteredTickets(Request $request, User $user)
    {
        $isManager = $this->isCompanyManager($user);
        $hasPermission = ($user->level === 'creator'
            || $isManager
            || $user->hasPermission('tickets.index')
            || $user->hasPermission('tickets.view')
            || $user->hasPermission('tickets.manage'));

        if (!$hasPermission) {
            return Ticket::whereRaw('1 = 0')->paginate(15);
        }

        $query = Ticket::with(['user', 'category', 'department', 'company', 'assigned']);
        $allowedCompanyIds = $this->allowedCompanyIds($user);

        if ($allowedCompanyIds !== null) {
            $query->where(function ($q) use ($allowedCompanyIds) {
                $q->whereIn('company_id', $allowedCompanyIds)
                  ->orWhereIn('target_company_id', $allowedCompanyIds);
            });

            if (!$isManager) {
                if ($user->level === 'user') {
                    $query->where('user_id', $user->id);
                } elseif ($user->department_id && !$user->hasPermission('tickets.view-all')) {
                    $query->where('department_id', $user->department_id);
                }
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->latest()->paginate(15)->withQueryString();
    }

    public function handleReply(Request $request, Ticket $ticket, User $user)
    {
        $isAuthor = ($ticket->user_id === $user->id);
        $isInternal = $request->boolean('is_internal');
        $isElevated = ($user->level === 'creator' || $this->isCompanyManager($user));

        if ($isInternal && !$isAuthor && !$isElevated && !$user->hasPermission('tickets.internal-notes')) {
            return ['status' => false, 'message' => 'شما مجوز ثبت یادداشت داخلی را ندارید.'];
        }

        if (!$isInternal) {
            $participantIds = $ticket->comments()->where('is_internal', false)->pluck('user_id')
                ->push($ticket->user_id)
                ->unique();

            if (!$participantIds->contains($user->id) && !$isAuthor && !$isElevated) {
                if ($participantIds->count() >= 2) {
                    return ['status' => false, 'message' => 'طبق قوانین، حداکثر ۲ نفر می‌توانند در این تیکت به صورت مستقیم گفتگو کنند.'];
                }
            }
        }

        $ticket->comments()->create([
            'user_id'     => $user->id,
            'message'     => $request->message,
            'is_internal' => $isInternal,
        ]);

        if (!$isInternal && !$isAuthor && $ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return ['status' => true, 'message' => 'پاسخ شما با موفقیت ثبت شد.'];
    }

    public function assignExpert(Request $request, Ticket $ticket, User $authUser): array
    {
        $targetUser = User::findOrFail($request->assigned_to);
        $allowedCompanyIds = $this->allowedCompanyIds($authUser);

        if ($allowedCompanyIds !== null) {
            $isTicketInScope = in_array((int) $ticket->company_id, $allowedCompanyIds, true)
                || in_array((int) $ticket->target_company_id, $allowedCompanyIds, true);

            if (!$isTicketInScope || !in_array((int) $targetUser->company_id, $allowedCompanyIds, true)) {
                return ['status' => false, 'message' => 'کارشناس انتخاب‌شده در محدوده شرکت‌های مجاز شما نیست.'];
            }
        }

        $ticket->update([
            'assigned_to'            => $targetUser->id,
            'last_sla_reminder_step' => -1,
            'sla_reminder_user_id'   => $targetUser->id,
            'last_sla_reminder_at'   => null,
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return ['status' => true, 'message' => 'پشتیبان مسئول با موفقیت تعیین شد.'];
    }

    public function storeComment(Request $request, Ticket $ticket, User $authUser): void
    {
        $comment = $ticket->comments()->create([
            'user_id' => $authUser->id,
            'message' => $request->body,
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('ticket-comment-attachments', 'public');

            $comment->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
            ]);
        }

        if ($ticket->status === 'open' && $ticket->user_id !== $authUser->id) {
            $ticket->update(['status' => 'in_progress']);
        }
    }

    public function updateTicket(Request $request, Ticket $ticket): void
    {
        $now = now();

        $ticket->update([
            'target_company_id'      => $request->target_company_id,
            'department_id'          => $request->department_id,
            'category_id'            => $request->category_id,
            'priority'               => $request->priority,
            'subject'                => $request->subject,
            'description'            => $request->message,
            'sla_started_at'         => $now,
            'last_sla_reminder_step' => -1,
            'last_sla_reminder_at'   => null,
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('ticket-attachments', 'public');

            $ticket->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
            ]);
        }
    }
}