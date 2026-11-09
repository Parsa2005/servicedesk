<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\Department;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TicketController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $user = auth()->user();

        $query = Ticket::with(['category', 'department', 'company', 'targetCompany', 'assigned', 'user'])
            ->where(function ($q) use ($user) {
                if ($user->company_id) {
                    $q->where('company_id', $user->company_id)
                      ->orWhere('target_company_id', $user->company_id)
                      ->orWhere('assigned_to', $user->id);
                } else {
                    $q->where('user_id', $user->id);
                }
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('subject', 'like', "%{$request->search}%");
        }

        $tickets = $query->latest()->paginate(10)->withQueryString();

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

    public function store(Request $request)
    {
        $this->authorize('create', Ticket::class);

        $user = auth()->user();
        $senderCompany = $user->company;

        $request->validate([
            'subject'           => ['required', 'string', 'max:255'],
            'target_company_id' => [
                'required',
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($senderCompany) {
                    if ($senderCompany) {
                        $targetCompany = Company::find($value);
                        if (!$senderCompany->canInteractWith($targetCompany)) {
                            $fail('شما مجاز به ارسال تیکت به این شرکت نیستید (متعلق به هرم سازمانی شما نیست).');
                        }
                    }
                }
            ],
            'department_id'     => [
                'required',
                'exists:departments,id',
                function ($attribute, $value, $fail) use ($request) {
                    $department = Department::find($value);
                    if ($department && $department->company_id != $request->target_company_id) {
                        $fail('دپارتمان انتخاب شده متعلق به شرکت مقصد نمی‌باشد.');
                    }
                }
            ],
            'assigned_to'       => ['nullable', 'exists:users,id'],
            'category_id'       => ['required', 'exists:ticket_categories,id'],
            'priority'          => ['required', 'in:low,normal,high,critical'],
            'message'           => ['required', 'string'],
            'attachments.*'     => ['nullable', 'file', 'max:20480'],
        ]);

        $hoursToAdd = match ($request->priority) {
            'critical' => 2,
            'high'     => 8,
            'normal'   => 24,
            'low'      => 48,
            default    => 24,
        };

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

        return redirect()->route('user.tickets.index')->with('success', 'تیکت پشتیبانی با موفقیت ثبت و ارسال شد.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);
        
        $ticket->load([
            'company', 
            'targetCompany', 
            'department', 
            'category', 
            'user', 
            'assigned', 
            'attachments',
            'comments.user',
            'comments.attachments'
        ]);

        $rootCompany = $ticket->targetCompany 
            ? $ticket->targetCompany->getRootCompany() 
            : ($ticket->company ? $ticket->company->getRootCompany() : null);

        $assignableUsers = collect();
        if ($rootCompany) {
            $groupCompanyIds = $rootCompany->getGroupCompanies()->pluck('id');
            $assignableUsers = User::whereIn('company_id', $groupCompanyIds)
                ->whereNotNull('department_id')
                ->get();
        } else {
            $assignableUsers = User::where('company_id', $ticket->target_company_id)
                ->whereNotNull('department_id')
                ->get();
        }

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

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $senderCompany = auth()->user()->company;

        $request->validate([
            'subject'           => ['required', 'string', 'max:255'],
            'target_company_id' => [
                'required',
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($senderCompany) {
                    if ($senderCompany) {
                        $targetCompany = Company::find($value);
                        if (!$senderCompany->canInteractWith($targetCompany)) {
                            $fail('شما مجاز به ارسال تیکت به این شرکت نیستید.');
                        }
                    }
                }
            ],
            'department_id'     => [
                'required',
                'exists:departments,id',
                function ($attribute, $value, $fail) use ($request) {
                    $department = Department::find($value);
                    if ($department && $department->company_id != $request->target_company_id) {
                        $fail('دپارتمان انتخاب شده متعلق به شرکت مقصد نمی‌باشد.');
                    }
                }
            ],
            'assigned_to'       => ['nullable', 'exists:users,id'],
            'category_id'       => ['required', 'exists:ticket_categories,id'],
            'priority'          => ['required', 'in:low,normal,high,critical'],
            'message'           => ['required', 'string'],
            'attachments.*'     => ['nullable', 'file', 'max:20480'],
        ]);

        $ticket->update([
            'target_company_id' => $request->target_company_id,
            'department_id'     => $request->department_id,
            'assigned_to'       => $request->assigned_to,
            'category_id'       => $request->category_id,
            'priority'          => $request->priority,
            'subject'           => $request->subject,
            'description'       => $request->message,
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

        return redirect()->route('user.tickets.show', $ticket)
            ->with('success', 'تیکت با موفقیت ویرایش شد.');
    }

    public function storeComment(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $request->validate([
            'body'           => ['required', 'string'],
            'assigned_to'    => ['nullable', 'exists:users,id'],
            'attachments'    => ['nullable', 'array'],
            'attachments.*'  => ['file', 'max:20480'],
        ]);

        $newAssigneeId = $request->input('assigned_to');
        $message = $request->body;

        
        if ($newAssigneeId && $newAssigneeId != $ticket->assigned_to) {
            $newAssignee = User::find($newAssigneeId);
            $message .= "\n\n[🔄 ارجاع سیستم: این تیکت توسط " . auth()->user()->name . " به " . $newAssignee->name . " (" . optional($newAssignee->department)->name . " - " . optional($newAssignee->company)->name . ") ارجاع داده شد.]";
        }

        $comment = $ticket->comments()->create([
            'user_id' => auth()->id(),
            'message' => $message,
        ]);

        if ($request->filled('assigned_to')) {
            $ticket->update([
                'assigned_to' => $request->assigned_to
            ]);
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

        if ($ticket->status === 'open' && $ticket->user_id !== auth()->id()) {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'پاسخ شما با موفقیت ثبت و ارجاع شد.');
    }
}