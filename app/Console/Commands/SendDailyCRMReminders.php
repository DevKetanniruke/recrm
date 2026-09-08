<?php

namespace App\Console\Commands;

use App\Models\PaymentSchedule;
use App\Models\SiteVisit;
use Illuminate\Console\Command;

class SendDailyCRMReminders extends Command
{
    protected $signature = 'crm:daily-reminders';
    protected $description = 'Scan overdue payment milestones and send automated notifications';

    public function handle(): int
    {
        $this->info('Starting CRM Daily Reminders Check...');

        // 1. Mark overdue payment milestones
        $overdueCount = PaymentSchedule::where('due_date', '<', now()->toDateString())
            ->whereIn('status', ['Pending', 'Partially Paid'])
            ->update(['status' => 'Overdue']);

        $this->info("Updated {$overdueCount} payment milestones to Overdue status.");

        // 2. Count today's site visits
        $todayVisits = SiteVisit::whereDate('visit_date', now()->toDateString())
            ->where('status', 'Scheduled')
            ->count();

        $this->info("Found {$todayVisits} site visits scheduled for today.");

        $this->info('Daily CRM Reminders completed successfully.');
        return Command::SUCCESS;
    }
}
