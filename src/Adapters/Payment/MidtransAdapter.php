<?php
namespace DonasiYuk\Adapters\Payment;

use DonasiYuk\Domain\Payment\PaymentGatewayInterface;

class MidtransAdapter implements PaymentGatewayInterface {
    private string $serverKey;
    private bool $isProduction;

    public function __construct(string $serverKey = '', bool $isProduction = false) {
        $this->serverKey = $serverKey ?: (defined('DYK_MIDTRANS_SERVER_KEY') ? DYK_MIDTRANS_SERVER_KEY : '');
        $this->isProduction = $isProduction;
    }

    public function getId(): string {
        return 'midtrans';
    }

    public function getDisplayName(): string {
        return 'Midtrans Payment Gateway';
    }

    public function createCharge(array $requestData): array {
        $orderId = $requestData['order_id'] ?? ('DYK-' . time() . '-' . rand(1000, 9999));
        $grossAmount = (int) ($requestData['amount'] ?? 0);

        $idempotencyKey = hash('sha256', $orderId . '-' . $grossAmount);

        $snapUrl = $this->isProduction 
            ? 'https://app.midtrans.com/snap/v2/vtweb/' 
            : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/';

        $snapToken = 'snap_' . md5($orderId . $this->serverKey);

        return [
            'success'         => true,
            'gateway'         => 'midtrans',
            'order_id'        => $orderId,
            'gross_amount'    => $grossAmount,
            'token'           => $snapToken,
            'redirect_url'    => $snapUrl . $snapToken,
            'idempotency_key' => $idempotencyKey,
        ];
    }

    public function verifyWebhookSignature(array $payload, string $signature): bool {
        if (!isset($payload['order_id'], $payload['status_code'], $payload['gross_amount'])) {
            return false;
        }
        $expected = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . $this->serverKey);
        return hash_equals($expected, $signature);
    }

    public function parseWebhook(array $payload): array {
        $trxStatus = $payload['transaction_status'] ?? 'pending';
        $fraudStatus = $payload['fraud_status'] ?? 'accept';

        $normalizedStatus = 'pending';
        if ($trxStatus === 'capture' || $trxStatus === 'settlement') {
            $normalizedStatus = ($fraudStatus === 'challenge') ? 'challenge' : 'paid';
        } elseif (in_array($trxStatus, ['cancel', 'deny', 'expire'], true)) {
            $normalizedStatus = 'failed';
        }

        return [
            'order_id'       => $payload['order_id'] ?? '',
            'transaction_id' => $payload['transaction_id'] ?? '',
            'status'         => $normalizedStatus,
            'raw_status'     => $trxStatus,
            'gross_amount'   => (float) ($payload['gross_amount'] ?? 0),
            'raw'            => $payload,
        ];
    }
}
