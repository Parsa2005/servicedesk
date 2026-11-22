<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\PanelTicketService;
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

    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $tickets = $this->panelTicketService->getFilteredTickets($request, auth()->user());

        return view('panel.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $user = auth()->user();

        if ($user->department_id && $ticket->department_id !== $user->department_id && !$user->hasPermission('tickets.view-all') && $ticket->user_id !== $user->id && $user->level !== 'creator') {
            return redirect()->route('panel.tickets.index')
                ->with('error', 'این تیکت به دپارتمان شما ارجاع داده نشده است.');
        }

        $companyStaff = User::where('company_id', $ticket->company_id)->get();

        $ticket->load(['comments.user.roles', 'comments.user.company', 'user.company', 'user.department', 'category', 'assigned']);

        return view('panel.tickets.show', compact('ticket', 'companyStaff'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $this->authorize('reply', $ticket);

        $request->validate([
            'message'     => ['required', 'string'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        $result = $this->panelTicketService->handleReply($request, $ticket, auth()->user());

        if (!$result['status']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    public function status(Request $request, Ticket $ticket)
    {
        $this->authorize('manage', $ticket);

        $request->validate([
            'status' => ['required', 'in:open,pending,in_progress,resolved,closed'],
        ]);

        $ticket->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'وضعیت تیکت با موفقیت بروزرسانی شد.');
    }

    public function close(Ticket $ticket)
    {
        $this->authorize('close', $ticket);

        if ($ticket->status === 'closed') {
            return back()->with('error', 'این تیکت از قبل بسته شده است.');
        }

        $ticket->update(['status' => 'closed']);

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

        return back()->with('success', $result['message']);
    }

    public function storeComment(Request $request, Ticket $ticket)
    {
        $this->authorize('reply', $ticket);

        $request->validate([
            'body'       => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:20480'],
        ]);

        $this->panelTicketService->storeComment($request, $ticket, auth()->user());

        return back()->with('success', 'پاسخ شما با موفقیت ثبت شد.');
    }

    public function edit(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        if (!$ticket->canBeEditedBy(auth()->user())) {
            return redirect()->route('user.tickets.show', $ticket)
                ->with('error', 'به دلیل ثبت پاسخ از سوی طرف مقابل، امکان ویرایش این تیکت وجود ندارد.');
        }

        $user = auth()->user();
        $senderCompany = $user->company;

        $allowedCompanies = $senderCompany ? $senderCompany->getGroupCompanies() : collect();
        $allowedCompanyIds = $allowedCompanies->pluck('id');

        $targetDepartments = Department::whereIn('company_id', $allowedCompanyIds)->get();
        $categories = TicketCategory::where('company_id', $user->company_id)->get();

        return view('user.tickets.edit', compact('ticket', 'categories', 'allowedCompanies', 'targetDepartments'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        if (!$ticket->canBeEditedBy(auth()->user())) {
            return redirect()->route('user.tickets.show', $ticket)
                ->with('error', 'امکان ویرایش این تیکت وجود ندارد.');
        }

        $request->validate([
            'subject'           => ['required', 'string', 'max:255'],
            'target_company_id' => ['required', 'exists:companies,id'],
            'department_id'     => ['required', 'exists:departments,id'],
            'category_id'       => ['required', 'exists:ticket_categories,id'],
            'priority'          => ['required', 'in:low,normal,high,critical'],
            'message'           => ['required', 'string'],
            'attachment'        => ['nullable', 'file', 'max:20480'],
        ]);

        $this->panelTicketService->updateTicket($request, $ticket);

        return redirect()->route('user.tickets.show', $ticket)
            ->with('success', 'تیکت با موفقیت ویرایش شد.');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        if ($ticket->status !== 'closed') {
            return back()->with('error', 'خطا: امکان حذف تیکت‌های باز یا در جریان وجود ندارد. تیکت حتماً باید بسته شده باشد.');
        }

        $ticket->delete();

        return redirect()->route('panel.tickets.index')->with('success', 'تیکت با موفقیت حذف شد.');
    }
}