<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Ticket::with(['category', 'department', 'company', 'assignee'])
            ->where('user_id', $user->id)
            ->where('company_id', $user->company_id);

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
        $user = auth()->user();

        // 👈 دریافت دسته‌بندی‌های مختص شرکت کاربر (قابل مشاهده توسط تمام دپارتمان‌های همان شرکت)
        $categories = TicketCategory::where('company_id', $user->company_id)->get();
            
        $departments = Department::where('company_id', $user->company_id)->get();
        
        $companyStaff = User::where('company_id', $user->company_id)->get();

        return view('user.tickets.create', compact('categories', 'departments', 'companyStaff'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'subject'       => ['required', 'string', 'max:255'],
            'category_id'   => [
                'required', 
                'exists:ticket_categories,id',
                // 🛡️ اطمینان از اینکه دسته‌بندی انتخاب‌شده متعلق به شرکت کاربر باشد
                function ($attribute, $value, $fail) use ($user) {
                    $category = TicketCategory::find($value);
                    if (!$category || $category->company_id !== $user->company_id) {
                        $fail('دسته‌بندی انتخاب‌شده معتبر نیست.');
                    }
                }
            ],
            'department_id' => ['nullable', 'exists:departments,id'],
            'priority'      => ['required', 'in:low,normal,high,critical'],
            'message'       => ['required', 'string'],
            'assigned_to'   => [
                'nullable', 
                'exists:users,id', 
                function ($attribute, $value, $fail) use ($request, $user) {
                    if ($value) {
                        $staff = User::find($value);
                        
                        if (!$staff || $staff->company_id !== $user->company_id) {
                            $fail('کارشناس انتخاب‌شده متعلق به شرکت شما نیست.');
                        }
                        
                        if ($request->filled('department_id') && $staff->department_id != $request->department_id) {
                            $fail('کارشناس انتخاب‌شده عضو دپارتمان انتخاب‌شده نیست.');
                        }
                    }
                },
            ],
        ]);

        $hoursToAdd = match ($request->priority) {
            'critical' => 2,
            'high'     => 8,
            'normal'   => 24,
            'low'      => 48,
            default    => 24,
        };

        Ticket::create([
            'company_id'    => $user->company_id,
            'user_id'       => $user->id,
            'department_id' => $request->department_id,
            'category_id'   => $request->category_id,
            'assigned_to'   => $request->assigned_to,
            'priority'      => $request->priority,
            'subject'       => $request->subject,
            'description'   => $request->message,
            'status'        => 'open',
            'sla_due_at'    => now()->addHours($hoursToAdd),
        ]);

        return redirect()->route('user.tickets.index')
            ->with('success', 'تیکت شما با موفقیت ثبت شد.');
    }

    public function show(Ticket $ticket)
    {
        $user = auth()->user();

        if ($ticket->user_id !== $user->id || $ticket->company_id !== $user->company_id) {
            abort(403, 'شما مجاز به مشاهده این تیکت نیستید.');
        }

        $ticket->load([
            'comments' => function($q) {
                $q->where('is_internal', false);
            }, 
            'comments.user', 
            'comments.attachments',
            'category', 
            'department',
            'assignee',
            'attachments' => function($q) {
                $q->whereNull('ticket_comment_id');
            }
        ]);

        return view('user.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        if ($ticket->user_id !== $user->id || $ticket->company_id !== $user->company_id) {
            abort(403, 'شما مجاز به پاسخ‌گویی به این تیکت نیستید.');
        }

        $request->validate([
            'message'    => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:2048'],
        ]);

        $comment = $ticket->comments()->create([
            'user_id'     => $user->id,
            'message'     => $request->message,
            'is_internal' => false,
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('ticket-attachments', 'public');

            $ticket->attachments()->create([
                'ticket_comment_id' => $comment->id,
                'file_path'         => $path,
                'file_name'         => $file->getClientOriginalName(),
            ]);
        }

        if ($ticket->status === 'resolved' || $ticket->status === 'closed') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'پاسخ شما با موفقیت ارسال شد.');
    }

    public function close(Ticket $ticket)
    {
        $user = auth()->user();

        if ($ticket->user_id !== $user->id || $ticket->company_id !== $user->company_id) {
            abort(403, 'شما مجاز به بستن این تیکت نیستید.');
        }

        if ($ticket->status === 'closed') {
            return back()->with('error', 'این تیکت از قبل بسته شده است.');
        }

        $ticket->update(['status' => 'closed']);

        return back()->with('success', 'تیکت با موفقیت بسته شد.');
    }
}