<?php

namespace App\Contracts;

class CommunicationResult
{
    public function __construct(
        public bool $success,
        public string $providerName,
        public ?string $providerReference = null,
        public ?string $errorMessage = null,
        public array $metadata = []
    ) {}

    public static function success(string $providerName, ?string $reference = null, array $metadata = []): self
    {
        return new self(
            success: true,
            providerName: $providerName,
            providerReference: $reference ?? 'REF-' . strtoupper(uniqid()),
            metadata: $metadata
        );
    }

    public static function failure(string $providerName, string $errorMessage, array $metadata = []): self
    {
        return new self(
            success: false,
            providerName: $providerName,
            errorMessage: $errorMessage,
            metadata: $metadata
        );
    }
}
