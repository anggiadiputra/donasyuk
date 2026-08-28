<?php
namespace DonasiYuk\Adapters\Payment;

use DonasiYuk\Domain\Payment\PaymentGatewayInterface;

class StripeAdapter implements PaymentGatewayInterface {
    private string $webhookSecret;

    public function __construct(string $webhookSecret = '') {
        $this->webhookSecret = $webhookSecret ?: (defined('DYK_STRIPE_WEBHOOK_SECRET') ? DYK_STRIPE_WEBHOOK_SECRET : '');
    }

    public function getId(): string { return 'stripe'; }
    public function getDisplayName(): string { return 'Stripe Payment Gateway'; }

    public function createCharge(array $requestData): array {
        return [
            'success' => true,
            'transaction_id' => 'STRIPE-' . time(),
            'payment_url' => 'https://checkout.stripe.com/pay/' . time(),
        ];
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool {
        if ($this->webhookSecret === '' || $signature === '') {
            return false;
        }

        $canonical = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($canonical === false) {
            return false;
        }

        $expected = hash_hmac('sha256', $canonical, $this->webhookSecret);
        return hash_equals($expected, ltrim(strtolower($signature), 'sha256='));
    }

    public function parseWebhook(array $payload): array {
        return [
            'transaction_id' => $payload['id'] ?? '',
            'status'         => 'paid',
            'amount'         => (float)($payload['amount'] ?? 0),
        ];
    }
}
