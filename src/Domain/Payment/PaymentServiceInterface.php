<?php
namespace DonasiYuk\Domain\Payment;

interface PaymentServiceInterface {
    public function registerGateway(PaymentGatewayInterface $gateway): void;
    public function getGateway(string $id): ?PaymentGatewayInterface;
    public function processCharge(string $gatewayId, array $requestData): array;
}
