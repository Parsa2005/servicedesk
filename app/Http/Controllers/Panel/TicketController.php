<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $hasPermission = ($user->level === 'creator' || $user->hasPermission('tickets.index') || $user->hasPermission('tickets.view') || $user->hasPermission('tickets.manage'));

        if (!$hasPermission) {
            $tickets = Ticket::whereRaw('1 = 0')->paginate(15);
            return view('panel.tickets.index', compact('tickets'));
        }

        $query = Ticket::with(['user', 'category', 'department', 'company', 'assignee']);

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

        $tickets = $query->latest()->paginate(15)->withQueryString();

        return view('panel.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        $user = auth()->user();

        if ($user->level !== 'creator' && $ticket->company_id !== $user->company_id) {
            abort(403, 'شما مجاز به مشاهده این تیکت نیستید.');
        }

        $isAuthor = ($ticket->user_id === $user->id);

        if (!$isAuthor && $user->level !== 'creator') {
            if ($user->department_id && $ticket->department_id !== $user->department_id && !$user->hasPermission('tickets.view-all')) {
                return redirect()->route('panel.tickets.index')
                    ->with('error', 'این تیکت به دپارتمان شما ارجاع داده نشده است.');
            }
        }

        // بارگذاری کارمندان همان شرکت برای باکس انتخاب پشتیبان مسئول
        $companyStaff = User::where('company_id', $ticket->company_id)->get();

        $ticket->load(['comments.user.roles', 'comments.user.company', 'user.company', 'user.department', 'category', 'assignee']);

        return view('panel.tickets.show', compact('ticket', 'companyStaff'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        if ($user->level !== 'creator' && $ticket->company_id !== $user->company_id) {
            abort(403, 'شما مجاز به پاسخ‌گویی به این تیکت نیستید.');
        }

        $isAuthor = ($ticket->user_id === $user->id);
        $hasReplyPermission = ($user->level === 'creator' || $user->hasPermission('tickets.reply'));

        if (!$isAuthor && !$hasReplyPermission) {
            abort(403, 'شما مجوز پاسخ‌گویی به این تیکت را ندارید.');
        }

        $request->validate([
            'message'     => ['required', 'string'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        $isInternal = $request->boolean('is_internal');

        if ($isInternal && !$isAuthor && $user->level !== 'creator' && !$user->hasPermission('tickets.internal-notes')) {
            abort(403, 'شما مجوز ثبت یادداشت داخلی را ندارید.');
        }

        if (!$isInternal) {
            $participantIds = $ticket->comments()->where('is_internal', false)->pluck('user_id')
                ->push($ticket->user_id)
                ->unique();

            if (!$participantIds->contains($user->id) && !$isAuthor && $user->level !== 'creator') {
                if ($participantIds->count() >= 2) {
                    return back()->with('error', 'طبق قوانین، حداکثر ۲ نفر می‌توانند در این تیکت به صورت مستقیم گفتگو کنند.');
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

        return back()->with('success', 'پاسخ شما با موفقیت ثبت شد.');
    }

    public function status(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        if ($user->level !== 'creator' && $ticket->company_id !== $user->company_id) {
            abort(403, 'شما مجاز به تغییر وضعیت این تیکت نیستید.');
        }

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
        $user = auth()->user();

        if ($user->level !== 'creator' && $ticket->company_id !== $user->company_id) {
            abort(403, 'شما مجاز به انجام این عملیات نیستید.');
        }

        $isAuthor = ($ticket->user_id === $user->id);
        $hasManagePermission = ($user->level === 'creator' || $user->hasPermission('tickets.manage') || $user->hasPermission('tickets.close') || $ticket->department_id === $user->department_id);

        if (!$isAuthor && !$hasManagePermission) {
            abort(403, 'شما مجوز بستن این تیکت را ندارید.');
        }

        if ($ticket->status === 'closed') {
            return back()->with('error', 'این تیکت از قبل بسته شده است.');
        }

        $ticket->update(['status' => 'closed']);

        return back()->with('success', 'تیکت با موفقیت بسته شد.');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        if ($user->level !== 'creator' && $ticket->company_id !== $user->company_id) {
            abort(403, 'شما مجاز به انجام این عملیات نیستید.');
        }

        $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $targetUser = User::findOrFail($request->assigned_to);

        if ($user->level !== 'creator' && $targetUser->company_id !== $ticket->company_id) {
            return back()->with('error', 'کارشناس انتخاب‌شده متعلق به این شرکت نیست.');
        }

        $ticket->update([
            'assigned_to' => $targetUser->id,
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'پشتیبان مسئول با موفقیت تعیین شد.');
    }
}