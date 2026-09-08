<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBookingReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function handle(): void
    {
        Log::info("Processing background booking confirmation and agreement document generation for Booking #{$this->booking->booking_number}");
        // Document generation / pdf storage logic can be triggered here
    }
}
