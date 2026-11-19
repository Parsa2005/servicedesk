<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class PanelTicketService
{
    public function getFilteredTickets(Request $request, User $user)
    {
        $hasPermission = ($user->level === 'creator' || $user->hasPermission('tickets.index') || $user->hasPermission('tickets.view') || $user->hasPermission('tickets.manage'));

        if (!$hasPermission) {
            return Ticket::whereRaw('1 = 0')->paginate(15);
        }

        $query = Ticket::with(['user', 'category', 'department', 'company', 'assigned']);

        if ($user->level !== 'creator') {
            $query->where('company_id', $user->company_id);

            if ($user->level === 'user') {
                $query->where('user_id', $user->id);
            } else {
                if ($user->department_id && !$user->hasPermission('tickets.view-all')) {
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

        if ($isInternal && !$isAuthor && $user->level !== 'creator' && !$user->hasPermission('tickets.internal-notes')) {
            return ['status' => false, 'message' => 'شما مجوز ثبت یادداشت داخلی را ندارید.'];
        }

        if (!$isInternal) {
            $participantIds = $ticket->comments()->where('is_internal', false)->pluck('user_id')
                ->push($ticket->user_id)
                ->unique();

            if (!$participantIds->contains($user->id) && !$isAuthor && $user->level !== 'creator') {
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

        if ($authUser->level !== 'creator' && $targetUser->company_id !== $ticket->company_id) {
            return ['status' => false, 'message' => 'کارشناس انتخاب‌شده متعلق به این شرکت نیست.'];
        }

        $ticket->update([
            'assigned_to' => $targetUser->id,
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
            'body'    => $request->body,
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
        $ticket->update([
            'target_company_id' => $request->target_company_id,
            'department_id'     => $request->department_id,
            'category_id'       => $request->category_id,
            'priority'          => $request->priority,
            'subject'           => $request->subject,
            'description'       => $request->message,
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