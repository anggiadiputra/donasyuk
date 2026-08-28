<?php
namespace DonasiYuk\Domain\Payment;

interface PaymentGatewayInterface {
    public function getId(): string;
    public function getDisplayName(): string;
    public function createCharge(array $requestData): array;
    public function verifyWebhookSignature(array $payload, string $signature): bool;
    public function parseWebhook(array $payload): array;
}
