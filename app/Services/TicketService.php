<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Company;
use App\Models\Sla;
use App\Notifications\TicketNotification;
use Illuminate\Http\Request;

class TicketService
{
    public function createTicket(Request $request, User $user): Ticket
    {
        // 🕒 محاسبه داینامیک و آبشاری ساعت SLA
        $hoursToAdd = $this->resolveSlaHours(
            $request->target_company_id, 
            $user->company_id, 
            $request->priority
        );

        $ticket = Ticket::create([
            'company_id'        => $user->company_id,      
            'target_company_id' => $request->target_company_id, 
            'user_id'           => $user->id,             
            'department_id'     => $request->department_id, 
            'assigned_to'       => $request->assigned_to, 
            'category_id'       => $request->category_id,
            'priority'          => $request->priority,
            'subject'           => $request->subject,
            'description'       => $request->message,
            'status'            => 'open',
            'sla_due_at'        => now()->addHours($hoursToAdd),
            'notified_milestones' => [],
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments', 'public');

                $ticket->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        if ($ticket->assigned_to && $ticket->assigned_to !== $user->id) {
            $assignedUser = User::find($ticket->assigned_to);
            if ($assignedUser) {
                $assignedUser->notify(new TicketNotification($ticket, 'یک تیکت جدید با موضوع "' . $ticket->subject . '" به شما ارجاع داده شد.'));
            }
        }

        return $ticket;
    }

    public function updateTicket(Request $request, Ticket $ticket): Ticket
    {
        // بروزرسانی زمان SLA در صورت تغییر شرکت مقصد یا اولویت تیکت
        $hoursToAdd = $this->resolveSlaHours(
            $request->target_company_id, 
            $ticket->company_id, 
            $request->priority
        );

        $ticket->update([
            'target_company_id' => $request->target_company_id,
            'department_id'     => $request->department_id,
            'assigned_to'       => $request->assigned_to,
            'category_id'       => $request->category_id,
            'priority'          => $request->priority,
            'subject'           => $request->subject,
            'description'       => $request->message,
            'sla_due_at'        => now()->addHours($hoursToAdd),
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-attachments', 'public');

                $ticket->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return $ticket;
    }

    public function addComment(Request $request, Ticket $ticket, User $authUser): array
    {
        $newAssigneeId = $request->input('assigned_to');
        $message = $request->body;

        if ($newAssigneeId && $newAssigneeId != $ticket->assigned_to) {
            $newAssignee = User::find($newAssigneeId);
            if ($newAssignee) {
                $message .= "\n\n[🔄 ارجاع سیستم: این تیکت توسط " . $authUser->name . " به " . $newAssignee->name . " (" . optional($newAssignee->department)->name . " - " . optional($newAssignee->company)->name . ") ارجاع داده شد.]";
            }
        }

        $comment = $ticket->comments()->create([
            'user_id' => $authUser->id,
            'message' => $message,
        ]);

        if ($request->filled('assigned_to')) {
            $ticket->update(['assigned_to' => $request->assigned_to]);
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ticket-comment-attachments', 'public');

                $comment->attachments()->create([
                    'ticket_id'         => $ticket->id,
                    'ticket_comment_id' => $comment->id,
                    'file_path'         => $path,
                    'file_name'         => $file->getClientOriginalName(),
                ]);
            }
        }

        if ($ticket->status === 'open' && $ticket->user_id !== $authUser->id) {
            $ticket->update(['status' => 'in_progress']);
        }

        if ($ticket->user_id !== $authUser->id) {
            $ticket->user->notify(new TicketNotification($ticket, 'کاربر ' . $authUser->name . ' به تیکت شما پاسخ داد.'));
        }

        if ($newAssigneeId && $newAssigneeId != $ticket->getOriginal('assigned_to') && $newAssigneeId != $authUser->id) {
            $newAssigneeUser = User::find($newAssigneeId);
            if ($newAssigneeUser) {
                $newAssigneeUser->notify(new TicketNotification($ticket, 'تیکت "' . $ticket->subject . '" به شما ارجاع داده شد.'));
            }
        }

        return [$ticket, $comment];
    }

    
    protected function resolveSlaHours(?int $targetCompanyId, ?int $fallbackCompanyId, string $priority): int
    {
        $companyId = $targetCompanyId ?? $fallbackCompanyId;

        if ($companyId) {
            $company = Company::find($companyId);
            
            while ($company) {
                $sla = Sla::where('company_id', $company->id)
                    ->where('priority', $priority)
                    ->first();

                if ($sla) {
                    return $sla->hours;
                }

                $company = $company->parent; 
            }
        }

        return match ($priority) {
            'critical' => 2,
            'high'     => 12,
            'normal'   => 48,
            'low'      => 72,
            default    => 48,
        };
    }
}