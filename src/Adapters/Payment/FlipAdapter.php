<?php
namespace DonasiYuk\Adapters\Payment;

use DonasiYuk\Domain\Payment\PaymentGatewayInterface;

class FlipAdapter implements PaymentGatewayInterface {
    private string $secretKey;

    public function __construct(string $secretKey = '') {
        $this->secretKey = $secretKey ?: (defined('DYK_FLIP_SECRET_KEY') ? DYK_FLIP_SECRET_KEY : '');
    }

    public function getId(): string { return 'flip'; }
    public function getDisplayName(): string { return 'Flip for Business'; }

    public function createCharge(array $requestData): array {
        return [
            'success' => true,
            'transaction_id' => 'FLIP-' . time(),
            'payment_url' => 'https://flip.id/bill/' . time(),
        ];
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool {
        if ($this->secretKey === '' || $signature === '') {
            return false;
        }

        $canonical = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($canonical === false) {
            return false;
        }

        $expected = hash_hmac('sha256', $canonical, $this->secretKey);
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
