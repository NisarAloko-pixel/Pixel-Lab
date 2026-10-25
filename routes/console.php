<?php

use App\Mail\ReportMail;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    if (!(bool) Setting::get('report_enabled', false)) {
        return;
    }

    $frequency = Setting::get('report_frequency', 'monthly');
    $today = Carbon::today();

    $shouldRunToday = match ($frequency) {
        'weekly' => $today->isMonday(),
        'monthly' => $today->isSameDay($today->copy()->startOfMonth()),
        'yearly' => $today->isSameDay($today->copy()->startOfYear()),
        default => false,
    };

    if (!$shouldRunToday) {
        return;
    }

    $emails = json_decode(Setting::get('report_emails', '[]'), true) ?: [];
    $types = json_decode(Setting::get('report_types', '[]'), true) ?: [];

    if (empty($emails) || empty($types)) {
        return;
    }

    [$from, $to] = match ($frequency) {
        'weekly' => [$today->copy()->subWeek()->startOfWeek(), $today->copy()->subWeek()->endOfWeek()],
        'yearly' => [$today->copy()->subYear()->startOfYear(), $today->copy()->subYear()->endOfYear()],
        default => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
    };

    foreach ($emails as $email) {
        Mail::to($email)->send(new ReportMail($types, $from, $to));
    }
})->daily()->name('send-scheduled-reports')->withoutOverlapping();

Schedule::call(function () {
    if (! (bool) Setting::get('backup_enabled', false)) {
        return;
    }

    $frequency = Setting::get('backup_frequency', 'daily');
    $today = Carbon::today();

    $shouldRunToday = match ($frequency) {
        'daily' => true,
        'weekly' => $today->isMonday(),
        'monthly' => $today->isSameDay($today->copy()->startOfMonth()),
        'yearly' => $today->isSameDay($today->copy()->startOfYear()),
        default => false,
    };

    if (! $shouldRunToday) {
        return;
    }

    Artisan::call('backup:run', ['--label' => 'scheduled']);
})->daily()->name('run-scheduled-backups')->withoutOverlapping();