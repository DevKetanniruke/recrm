<?php

namespace App\Services\Communication\Providers;

use App\Contracts\CommunicationProviderInterface;
use App\Contracts\CommunicationResult;
use Illuminate\Support\Facades\Log;

class MockSmsProvider implements CommunicationProviderInterface
{
    public function getProviderName(): string
    {
        return 'Twilio SMS (Mock Driver)';
    }

    public function getSupportedChannel(): string
    {
        return 'sms';
    }

    public function send(string $recipient, string $message, ?string $subject = null, array $metadata = []): CommunicationResult
    {
        Log::info("MockSmsProvider [{$this->getProviderName()}]: Sending SMS to {$recipient} | Body: {$message}");

        return CommunicationResult::success(
            providerName: $this->getProviderName(),
            reference: 'TW-SMS-' . strtoupper(bin2hex(random_bytes(6)))
        );
    }
}
