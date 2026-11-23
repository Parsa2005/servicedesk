<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\Department;
use App\Models\Company;
use App\Models\User;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\StoreCommentRequest;
use App\Services\SlaReminderService;
use App\Services\TicketService;
use App\Notifications\TicketNotification;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TicketController extends Controller
{
    use AuthorizesRequests;

    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $user = auth()->user();
        $isManager = $user->roles()->where('slug', 'company-manager')->exists();

        $allowedCompanyIds = ($isManager && $user->company)
            ? $user->company->getDescendantCompanyIds()
            : ($user->company_id ? [(int) $user->company_id] : []);

        $query = Ticket::with(['category', 'department', 'company', 'targetCompany', 'assigned', 'user'])
            ->where(function ($q) use ($user, $allowedCompanyIds) {
                if (!empty($allowedCompanyIds)) {
                    $q->whereIn('company_id', $allowedCompanyIds)
                        ->orWhereIn('target_company_id', $allowedCompanyIds)
                        ->orWhere('assigned_to', $user->id);
                } else {
                    $q->where('user_id', $user->id)
                        ->orWhere('assigned_to', $user->id);
                }
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('subject', 'like', "%{$request->search}%");
        }

        $tickets = $query->latest()->paginate(10)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'data' => $tickets]);
        }

        return view('user.tickets.index', compact('tickets'));
    }

    public function create()
    {
        $this->authorize('create', Ticket::class);

        $user = auth()->user();
        $senderCompany = $user->company;

        $allowedCompanies = $senderCompany ? $senderCompany->getGroupCompanies() : Company::all();
        $allowedCompanyIds = $allowedCompanies->pluck('id');

        $targetDepartments = Department::whereIn('company_id', $allowedCompanyIds)->get();
        $departmentUsers = User::whereNotNull('department_id')->get();
        $categories = TicketCategory::all();

        return view('user.tickets.create', compact('categories', 'allowedCompanies', 'targetDepartments', 'departmentUsers'));
    }

    public function store(StoreTicketRequest $request)
    {
        $this->authorize('create', Ticket::class);

        $ticket = $this->ticketService->createTicket($request, auth()->user());

        if ($ticket instanceof Ticket && $ticket->assigned_to && $ticket->sla_due_at) {
            app(SlaReminderService::class)->sendInitialReminder($ticket->fresh());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'تیکت پشتیبانی با موفقیت ثبت و ارسال شد.',
                'data'    => $ticket
            ], 201);
        }

        return redirect()->route('user.tickets.index')->with('success', 'تیکت پشتیبانی با موفقیت ثبت و ارسال شد.');
    }

    public function show(Ticket $ticket, Request $request)
    {
        $this->authorize('view', $ticket);

        auth()->user()->unreadNotifications()
            ->where('data->ticket_id', $ticket->id)
            ->update(['read_at' => now()]);

        $ticket->load([
            'company', 'targetCompany', 'department', 'category', 'user', 'assigned', 'attachments', 'comments.user', 'comments.attachments'
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'data' => $ticket]);
        }

        $rootCompany = $ticket->targetCompany
            ? $ticket->targetCompany->getRootCompany()
            : ($ticket->company ? $ticket->company->getRootCompany() : null);

        $assignableUsers = $rootCompany
            ? User::whereIn('company_id', $rootCompany->getGroupCompanies()->pluck('id'))->whereNotNull('department_id')->get()
            : User::where('company_id', $ticket->target_company_id)->whereNotNull('department_id')->get();

        return view('user.tickets.show', compact('ticket', 'assignableUsers'));
    }

    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $user = auth()->user();
        $senderCompany = $user->company;

        $allowedCompanies = $senderCompany ? $senderCompany->getGroupCompanies() : Company::all();
        $allowedCompanyIds = $allowedCompanies->pluck('id');

        $targetDepartments = Department::whereIn('company_id', $allowedCompanyIds)->get();
        $departmentUsers = User::whereNotNull('department_id')->get();
        $categories = TicketCategory::all();

        return view('user.tickets.edit', compact('ticket', 'categories', 'allowedCompanies', 'targetDepartments', 'departmentUsers'));
    }

    public function update(StoreTicketRequest $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $this->ticketService->updateTicket($request, $ticket);

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'تیکت با موفقیت ویرایش شد.',
                'data'    => $ticket
            ]);
        }

        return redirect()->route('user.tickets.show', $ticket)
            ->with('success', 'تیکت با موفقیت ویرایش شد.');
    }

    public function storeComment(StoreCommentRequest $request, Ticket $ticket)
    {
        list($updatedTicket, $comment) = $this->ticketService->addComment($request, $ticket, auth()->user());

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'پاسخ شما با موفقیت ثبت و ارجاع شد.',
                'data'    => $comment->load('attachments', 'user')
            ], 201);
        }

        return back()->with('success', 'پاسخ شما با موفقیت ثبت و ارجاع شد.');
    }

    public function close(Ticket $ticket, Request $request)
    {
        $this->authorize('close', $ticket);

        if ($ticket->status === 'closed') {
            if ($request->expectsJson()) {
                return response()->json(['status' => false, 'message' => 'این تیکت از قبل بسته شده است.'], 400);
            }
            return back()->with('error', 'این تیکت از قبل بسته شده است.');
        }

        $ticket->update(['status' => 'closed']);

        if ($ticket->user_id !== auth()->id()) {
            $ticket->user->notify(new TicketNotification($ticket, 'تیکت شماره #' . $ticket->id . ' بسته شد.'));
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'تیکت با موفقیت بسته شد.']);
        }

        return back()->with('success', 'تیکت با موفقیت بسته شد.');
    }
}