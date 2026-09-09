<?php

namespace App\Services\Communication;

use App\Contracts\CommunicationProviderInterface;
use App\Services\Communication\Providers\MockEmailProvider;
use App\Services\Communication\Providers\MockInAppProvider;
use App\Services\Communication\Providers\MockSmsProvider;
use App\Services\Communication\Providers\MockWhatsAppProvider;
use InvalidArgumentException;

class CommunicationChannelManager
{
    protected array $providers = [];

    public function __construct()
    {
        // Register default mock drivers
        $this->registerProvider('email', new MockEmailProvider());
        $this->registerProvider('sms', new MockSmsProvider());
        $this->registerProvider('whatsapp', new MockWhatsAppProvider());
        $this->registerProvider('in_app', new MockInAppProvider());
    }

    /**
     * Register or override a provider for a specific channel
     */
    public function registerProvider(string $channel, CommunicationProviderInterface $provider): void
    {
        $this->providers[strtolower($channel)] = $provider;
    }

    /**
     * Resolve provider driver for given channel
     */
    public function getProvider(string $channel): CommunicationProviderInterface
    {
        $channelKey = strtolower($channel);
        if (!isset($this->providers[$channelKey])) {
            throw new InvalidArgumentException("No communication provider registered for channel [{$channel}].");
        }

        return $this->providers[$channelKey];
    }
}
