<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketNotification extends Notification
{
    use Queueable;

    public $ticket;
    public $messageText;

    public function __construct($ticket, $messageText)
    {
        $this->ticket = $ticket;
        $this->messageText = $messageText;
    }

    // ذخیره اعلان در دیتابیس
    public function via($notifiable)
    {
        return ['database'];
    }

    // داده‌هایی که درون دیتابیس (فیلد data) ذخیره می‌شوند
    public function toArray($notifiable)
    {
        return [
            'ticket_id' => $this->ticket->id,
            'subject'   => $this->ticket->subject,
            'message'   => $this->messageText,
            'url'       => route('user.tickets.show', $this->ticket->id),
        ];
    }
}