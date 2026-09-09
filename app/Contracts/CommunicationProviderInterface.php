<?php

namespace App\Contracts;

interface CommunicationProviderInterface
{
    /**
     * Get the name of the provider implementation (e.g., SendGrid, Twilio, MetaWhatsApp)
     */
    public function getProviderName(): string;

    /**
     * Get the channel supported by this provider (email, sms, whatsapp, in_app)
     */
    public function getSupportedChannel(): string;

    /**
     * Send a communication message
     */
    public function send(string $recipient, string $message, ?string $subject = null, array $metadata = []): CommunicationResult;
}
