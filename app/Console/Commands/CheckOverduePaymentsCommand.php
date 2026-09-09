<?php

namespace App\Console\Commands;

use App\Services\FinancialService;
use Illuminate\Console\Command;

class CheckOverduePaymentsCommand extends Command
{
    protected $signature = 'crm:check-overdue';
    protected $description = 'Automated overdue calculation worker scanning payment schedules past due date';

    public function handle(FinancialService $financialService): int
    {
        $this->info('Scanning payment schedules for overdue items...');
        $count = $financialService->checkAndMarkOverdueSchedules();
        $this->info("Successfully processed {$count} overdue payment schedule item(s).");

        return Command::SUCCESS;
    }
}
