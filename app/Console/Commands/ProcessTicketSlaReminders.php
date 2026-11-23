<?php

namespace App\Console\Commands;

use App\Services\SlaReminderService;
use Illuminate\Console\Command;

class ProcessTicketSlaReminders extends Command
{
    /**
     * نام و امضای دستور Artisan
     *
     * @var string
     */
    protected $signature = 'tickets:process-sla-reminders';

    /**
     * توضیح دستور در لیست artisan
     *
     * @var string
     */
    protected $description = 'بررسی مهلت SLA تیکت‌ها و ارسال یادآوری‌های اولیه و نصف‌شونده برای کارشناس مسئول';

    public function handle(SlaReminderService $reminderService): int
    {
        $sentCount = $reminderService->processDueReminders();

        $this->info("پردازش یادآوری‌های SLA انجام شد. تعداد یادآوری‌های ارسال‌شده: {$sentCount}");

        return self::SUCCESS;
    }
}