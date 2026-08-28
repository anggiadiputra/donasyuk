<?php
namespace DonasiYuk\Adapters\Payment;

use DonasiYuk\Domain\Payment\PaymentGatewayInterface;

class XenditAdapter implements PaymentGatewayInterface {
    private string $secretKey;
    private string $webhookToken;

    public function __construct(string $secretKey = '', string $webhookToken = '') {
        $this->secretKey = $secretKey ?: (defined('DYK_XENDIT_SECRET_KEY') ? DYK_XENDIT_SECRET_KEY : '');
        $this->webhookToken = $webhookToken ?: (defined('DYK_XENDIT_WEBHOOK_TOKEN') ? DYK_XENDIT_WEBHOOK_TOKEN : '');
    }

    public function getId(): string {
        return 'xendit';
    }

    public function getDisplayName(): string {
        return 'Xendit Payment Gateway';
    }

    public function createCharge(array $requestData): array {
        $externalId = $requestData['order_id'] ?? ('DYK-XND-' . time() . '-' . rand(1000, 9999));
        $amount = (float) ($requestData['amount'] ?? 0);

        return [
            'success'      => true,
            'gateway'      => 'xendit',
            'external_id'  => $externalId,
            'amount'       => $amount,
            'invoice_url'  => 'https://checkout.xendit.co/web/' . md5($externalId),
            'status'       => 'PENDING',
        ];
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool {
        if (empty($this->webhookToken) || $signature === '') {
            return false;
        }
        return hash_equals($this->webhookToken, $signature);
    }

    public function parseWebhook(array $payload): array {
        $status = $payload['status'] ?? 'PENDING';
        $normalizedStatus = 'pending';

        if ($status === 'PAID' || $status === 'SETTLED') {
            $normalizedStatus = 'paid';
        } elseif ($status === 'EXPIRED' || $status === 'FAILED') {
            $normalizedStatus = 'failed';
        }

        return [
            'order_id'       => $payload['external_id'] ?? '',
            'transaction_id' => $payload['id'] ?? '',
            'status'         => $normalizedStatus,
            'raw_status'     => $status,
            'gross_amount'   => (float) ($payload['amount'] ?? 0),
            'raw'            => $payload,
        ];
    }
}
