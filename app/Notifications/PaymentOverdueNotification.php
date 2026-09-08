<?php

namespace App\Notifications;

use App\Models\PaymentSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentOverdueNotification extends Notification
{
    use Queueable;

    public PaymentSchedule $schedule;

    public function __construct(PaymentSchedule $schedule)
    {
        $this->schedule = $schedule;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'schedule_id' => $this->schedule->id,
            'booking_number' => $this->schedule->booking->booking_number ?? 'N/A',
            'milestone_name' => $this->schedule->milestone_name,
            'amount_due' => $this->schedule->amount_due,
            'message' => "Payment milestone {$this->schedule->milestone_name} is overdue.",
        ];
    }
}
