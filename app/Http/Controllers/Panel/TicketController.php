<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Notifications\TicketNotification;
use App\Services\PanelTicketService;
use App\Services\SlaReminderService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TicketController extends Controller
{
    use AuthorizesRequests;

    protected $panelTicketService;

    public function __construct(PanelTicketService $panelTicketService)
    {
        $this->panelTicketService = $panelTicketService;
    }

    public function close(Ticket $ticket)
    {
        $this->authorize('close', $ticket);

        if ($ticket->status === 'closed') {
            return back()->with('error', 'این تیکت از قبل بسته شده است.');
        }

        $ticket->update(['status' => 'closed']);

        if ($ticket->user_id !== auth()->id() && $ticket->user) {
            $ticket->user->notify(new TicketNotification($ticket, 'تیکت شماره #' . $ticket->id . ' بسته شد.'));
        }

        return back()->with('success', 'تیکت با موفقیت بسته شد.');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $this->authorize('manage', $ticket);

        $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $result = $this->panelTicketService->assignExpert($request, $ticket, auth()->user());

        if (!$result['status']) {
            return back()->with('error', $result['message']);
        }

        $freshTicket = $ticket->fresh();
        if ($freshTicket && $freshTicket->assigned_to && $freshTicket->sla_due_at) {
            app(SlaReminderService::class)->sendInitialReminder($freshTicket);
        }

        return back()->with('success', $result['message']);
    }
}