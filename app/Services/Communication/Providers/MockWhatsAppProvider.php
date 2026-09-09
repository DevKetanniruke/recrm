<?php

namespace App\Services\Communication\Providers;

use App\Contracts\CommunicationProviderInterface;
use App\Contracts\CommunicationResult;
use Illuminate\Support\Facades\Log;

class MockWhatsAppProvider implements CommunicationProviderInterface
{
    public function getProviderName(): string
    {
        return 'Meta WhatsApp Business API (Mock Driver)';
    }

    public function getSupportedChannel(): string
    {
        return 'whatsapp';
    }

    public function send(string $recipient, string $message, ?string $subject = null, array $metadata = []): CommunicationResult
    {
        Log::info("MockWhatsAppProvider [{$this->getProviderName()}]: Sending WhatsApp to {$recipient}");

        return CommunicationResult::success(
            providerName: $this->getProviderName(),
            reference: 'WA-MSG-' . strtoupper(bin2hex(random_bytes(6)))
        );
    }
}
