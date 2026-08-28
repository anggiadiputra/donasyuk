<?php
namespace DonasiYuk\Domain\WhatsApp;

interface WhatsAppServiceInterface {
    public function registerProvider(WhatsAppProviderInterface $provider): void;
    public function send(string $to, string $template, array $vars, string $providerId = 'wanotif'): array;
}
