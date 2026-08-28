<?php
namespace DonasiYuk\Adapters\Payment;

use DonasiYuk\Domain\Payment\PaymentGatewayInterface;

class TripayAdapter implements PaymentGatewayInterface {
    private string $apiKey;

    public function __construct(string $apiKey = '') {
        $this->apiKey = $apiKey ?: (defined('DYK_TRIPAY_API_KEY') ? DYK_TRIPAY_API_KEY : '');
    }

    public function getId(): string { return 'tripay'; }
    public function getDisplayName(): string { return 'Tripay Payment Gateway'; }

    public function createCharge(array $requestData): array {
        return [
            'success' => true,
            'transaction_id' => 'TRIPAY-' . time(),
            'payment_url' => 'https://tripay.co.id/checkout/' . time(),
        ];
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool {
        if ($this->apiKey === '' || $signature === '') {
            return false;
        }

        $canonical = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($canonical === false) {
            return false;
        }

        $expected = hash_hmac('sha256', $canonical, $this->apiKey);
        return hash_equals($expected, ltrim(strtolower($signature), 'sha256='));
    }

    public function parseWebhook(array $payload): array {
        return [
            'transaction_id' => $payload['merchant_ref'] ?? '',
            'status'         => 'paid',
            'amount'         => (float)($payload['total_amount'] ?? 0),
        ];
    }
}
