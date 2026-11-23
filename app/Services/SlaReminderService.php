<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketNotification;
use Carbon\Carbon;

class SlaReminderService
{
    protected float $minRemainingMinutes = 5.0;

    public function sendInitialReminder(Ticket $ticket): void
    {
        $assignee = $this->resolveAssignee($ticket);

        if (!$assignee || !$ticket->sla_due_at) {
            return;
        }

        $startAt = $this->resolveStartTime($ticket);
        $dueAt = Carbon::parse($ticket->sla_due_at);
        $totalMinutes = $startAt->diffInMinutes($dueAt, false);

        if ($totalMinutes <= 0 || now()->greaterThanOrEqualTo($dueAt)) {
            return;
        }

        $now = now();
        $formattedTime = $this->formatDuration($totalMinutes);

        $ticket->update([
            'sla_started_at'         => $ticket->sla_started_at ?? $startAt,
            'last_sla_reminder_step' => 0,
            'sla_reminder_user_id'   => $assignee->id,
            'last_sla_reminder_at'   => $now,
        ]);

        $message = "تیکت #{$ticket->id} ({$ticket->subject}) به شما ارجاع شد؛ {$formattedTime} مهلت پاسخگویی دارید.";

        $assignee->notify(new TicketNotification($ticket, $message));
    }

    public function processDueReminders(): int
    {
        $sentCount = 0;

        $tickets = Ticket::with(['assigned', 'comments'])
            ->whereNotNull('assigned_to')
            ->whereNotNull('sla_due_at')
            ->whereNotIn('status', ['closed', 'resolved'])
            ->get();

        foreach ($tickets as $ticket) {
            $assignee = $this->resolveAssignee($ticket);
            if (!$assignee) {
                continue;
            }

            if ((int) $ticket->sla_reminder_user_id !== (int) $assignee->id) {
                $ticket->update([
                    'last_sla_reminder_step' => -1,
                    'sla_reminder_user_id'   => $assignee->id,
                    'last_sla_reminder_at'   => null,
                ]);
                $ticket->refresh();

                $this->sendInitialReminder($ticket);
                $ticket->refresh();
                $sentCount++;
                continue;
            }

            if ($this->hasAssigneeRepliedAfterLastReminder($ticket, $assignee->id)) {
                continue;
            }

            $now = now();
            $startAt = $this->resolveStartTime($ticket);
            $dueAt = Carbon::parse($ticket->sla_due_at);
            $totalMinutes = $startAt->diffInMinutes($dueAt, false);

            if ($totalMinutes <= 0 || $now->greaterThanOrEqualTo($dueAt)) {
                continue;
            }

            $elapsedMinutes = max(0, $startAt->diffInMinutes($now, false));

            $currentStep = (int) $ticket->last_sla_reminder_step;
            $nextStep = $currentStep + 1;

            $remainingAtNextStep = $totalMinutes / (2 ** $nextStep);

            if ($remainingAtNextStep < $this->minRemainingMinutes) {
                continue;
            }

            $triggerAt = $dueAt->copy()->subMinutes($remainingAtNextStep);

            if ($now->greaterThanOrEqualTo($triggerAt)) {
                $formattedElapsed = $this->formatDuration($elapsedMinutes);
                $formattedRemaining = $this->formatDuration($remainingAtNextStep);

                $ticket->update([
                    'sla_started_at'         => $ticket->sla_started_at ?? $startAt,
                    'last_sla_reminder_step' => $nextStep,
                    'sla_reminder_user_id'   => $assignee->id,
                    'last_sla_reminder_at'   => $now,
                ]);

                $message = "یادآوری مهلت پاسخگویی به تیکت #{$ticket->id} ({$ticket->subject}): {$formattedElapsed} از شروع مهلت گذشته و {$formattedRemaining} باقی مانده است.";

                $assignee->notify(new TicketNotification($ticket, $message));

                $sentCount++;
            }
        }

        return $sentCount;
    }

    protected function resolveAssignee(Ticket $ticket): ?User
    {
        return $ticket->assigned ?? User::find($ticket->assigned_to);
    }

    protected function resolveStartTime(Ticket $ticket): Carbon
    {
        if (!empty($ticket->sla_started_at)) {
            return Carbon::parse($ticket->sla_started_at);
        }

        return Carbon::parse($ticket->created_at);
    }

    protected function hasAssigneeRepliedAfterLastReminder(Ticket $ticket, int $assigneeId): bool
    {
        if ($ticket->last_sla_reminder_step < 0 || !$ticket->last_sla_reminder_at) {
            return false;
        }

        if (!$ticket->relationLoaded('comments')) {
            return false;
        }

        $lastReminderAt = Carbon::parse($ticket->last_sla_reminder_at);

        return $ticket->comments
            ->where('user_id', $assigneeId)
            ->where('is_internal', false)
            ->filter(fn ($comment) => Carbon::parse($comment->created_at)->greaterThan($lastReminderAt))
            ->isNotEmpty();
    }

    protected function formatDuration(float $minutes): string
    {
        $totalMins = max(1, (int) round($minutes));

        if ($totalMins >= 60) {
            $hours = intdiv($totalMins, 60);
            $mins = $totalMins % 60;

            if ($mins === 0) {
                return "{$hours} ساعت";
            }

            return "{$hours} ساعت و {$mins} دقیقه";
        }

        return "{$totalMins} دقیقه";
    }
}