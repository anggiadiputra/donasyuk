<?php
namespace DonasiYuk\Domain\Payment;

class PaymentService implements PaymentServiceInterface {
    /** @var array<string, PaymentGatewayInterface> */
    private array $gateways = [];

    public function registerGateway(PaymentGatewayInterface $gateway): void {
        $this->gateways[$gateway->getId()] = $gateway;
    }

    public function getGateway(string $id): ?PaymentGatewayInterface {
        return $this->gateways[$id] ?? null;
    }

    public function processCharge(string $gatewayId, array $requestData): array {
        $gateway = $this->getGateway($gatewayId);
        if (!$gateway) {
            return [
                'success' => false,
                'message' => "Payment gateway '{$gatewayId}' not supported.",
            ];
        }
        return $gateway->createCharge($requestData);
    }
}
