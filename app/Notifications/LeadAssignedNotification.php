<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadAssignedNotification extends Notification
{
    use Queueable;

    public Lead $lead;

    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Lead Assigned: {$this->lead->full_name}")
            ->line("You have been assigned a new lead: {$this->lead->full_name}")
            ->line("Phone: {$this->lead->phone}")
            ->line("Rating: {$this->lead->rating}")
            ->action('View Lead Details', route('leads.show', $this->lead->id))
            ->line('Please follow up promptly!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->full_name,
            'message' => "New lead {$this->lead->full_name} assigned to you.",
        ];
    }
}
