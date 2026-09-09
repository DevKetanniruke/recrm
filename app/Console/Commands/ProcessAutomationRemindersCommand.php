<?php

namespace App\Console\Commands;

use App\Models\LeadFollowup;
use App\Models\PaymentSchedule;
use App\Models\SiteVisit;
use App\Services\Automation\AutomationEngineService;
use Illuminate\Console\Command;

class ProcessAutomationRemindersCommand extends Command
{
    protected $signature = 'crm:process-automation-reminders';
    protected $description = 'Worker scanning upcoming site visits, payment due dates, and follow-ups to trigger automation rules';

    public function handle(AutomationEngineService $automationEngine): int
    {
        $this->info('Scanning site visits, payment schedules, and follow-ups for automated reminders...');
        $totalTriggered = 0;

        // 1. Site Visit Reminders (Upcoming in next 24 hours)
        $upcomingVisits = SiteVisit::with('lead')
            ->whereIn('status', ['Scheduled', 'Confirmed'])
            ->whereBetween('visit_date', [now(), now()->addHours(24)])
            ->get();

        foreach ($upcomingVisits as $visit) {
            $totalTriggered += $automationEngine->trigger('site_visit.scheduled', $visit);
        }

        // 2. Payment Due Reminders (Due in next 7 days)
        $dueSchedules = PaymentSchedule::with('booking.customer')
            ->where('status', 'Pending')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->get();

        foreach ($dueSchedules as $schedule) {
            $totalTriggered += $automationEngine->trigger('payment.due', $schedule);
        }

        // 3. Payment Overdue Alerts
        $overdueSchedules = PaymentSchedule::with('booking.customer')
            ->where('status', 'Overdue')
            ->get();

        foreach ($overdueSchedules as $schedule) {
            $totalTriggered += $automationEngine->trigger('payment.overdue', $schedule);
        }

        // 4. Follow-up Due Reminders
        $dueFollowups = LeadFollowup::with('lead')
            ->where('status', 'Pending')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($dueFollowups as $followup) {
            $totalTriggered += $automationEngine->trigger('followup.due', $followup);
        }

        $this->info("Successfully triggered {$totalTriggered} automated reminder action(s).");
        return Command::SUCCESS;
    }
}
