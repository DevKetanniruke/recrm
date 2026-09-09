<?php

namespace App\Services\Communication\Providers;

use App\Contracts\CommunicationProviderInterface;
use App\Contracts\CommunicationResult;
use Illuminate\Support\Facades\Log;

class MockEmailProvider implements CommunicationProviderInterface
{
    public function getProviderName(): string
    {
        return 'SendGrid (Mock Driver)';
    }

    public function getSupportedChannel(): string
    {
        return 'email';
    }

    public function send(string $recipient, string $message, ?string $subject = null, array $metadata = []): CommunicationResult
    {
        Log::info("MockEmailProvider [{$this->getProviderName()}]: Sending email to {$recipient} | Subject: {$subject}");
        
        return CommunicationResult::success(
            providerName: $this->getProviderName(),
            reference: 'SG-MAIL-' . strtoupper(bin2hex(random_bytes(6)))
        );
    }
}
