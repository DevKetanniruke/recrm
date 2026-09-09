<?php

namespace App\Services\Communication\Providers;

use App\Contracts\CommunicationProviderInterface;
use App\Contracts\CommunicationResult;
use Illuminate\Support\Facades\Log;

class MockInAppProvider implements CommunicationProviderInterface
{
    public function getProviderName(): string
    {
        return 'CRM In-App Notification Engine';
    }

    public function getSupportedChannel(): string
    {
        return 'in_app';
    }

    public function send(string $recipient, string $message, ?string $subject = null, array $metadata = []): CommunicationResult
    {
        Log::info("MockInAppProvider [{$this->getProviderName()}]: Sending In-App notification to user/recipient {$recipient}");

        return CommunicationResult::success(
            providerName: $this->getProviderName(),
            reference: 'INAPP-' . strtoupper(bin2hex(random_bytes(6)))
        );
    }
}
