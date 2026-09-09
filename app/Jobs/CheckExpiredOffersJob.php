<?php

namespace App\Jobs;

use App\Models\Offer;
use App\Services\OfferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckExpiredOffersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(OfferService $offerService): void
    {
        $expiredOffers = Offer::whereIn('status', ['Draft', 'Pending Manager Approval', 'Pending Admin Approval', 'Approved', 'Countered'])
            ->where(function ($q) {
                $q->where('valid_until', '<', now())
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('unit_lock_expires_at')
                         ->where('unit_lock_expires_at', '<', now());
                  });
            })
            ->get();

        foreach ($expiredOffers as $offer) {
            $offerService->releaseExpiredLock($offer);
        }
    }
}
