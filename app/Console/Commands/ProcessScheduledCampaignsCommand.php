<?php

namespace App\Console\Commands;

use App\Jobs\ExecuteCampaignJob;
use App\Models\Campaign;
use Illuminate\Console\Command;

class ProcessScheduledCampaignsCommand extends Command
{
    protected $signature = 'crm:process-scheduled-campaigns';
    protected $description = 'Worker to scan and execute scheduled marketing campaigns past their scheduled date';

    public function handle(): int
    {
        $this->info('Scanning scheduled campaigns...');

        $campaigns = Campaign::where('status', 'Scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($campaigns as $campaign) {
            ExecuteCampaignJob::dispatch($campaign);
            $count++;
        }

        $this->info("Dispatched {$count} scheduled campaign(s) for execution.");
        return Command::SUCCESS;
    }
}
