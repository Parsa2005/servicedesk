<?php

use App\Console\Commands\ProcessTicketSlaReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ProcessTicketSlaReminders::class)
    ->everyMinute()
    ->withoutOverlapping(5);