<?php
namespace DonasiYuk\Domain\Webhook;

interface WebhookDispatcherInterface {
    public function dispatch(string $event, array $payload, string $targetUrl, string $secret = ''): array;
}
