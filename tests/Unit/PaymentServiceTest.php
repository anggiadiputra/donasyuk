<?php
namespace DonasiYuk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use DonasiYuk\Domain\Payment\PaymentService;
use DonasiYuk\Adapters\Payment\MidtransAdapter;

class PaymentServiceTest extends TestCase {
    public function testProcessChargeWithRegisteredGateway() {
        $service = new PaymentService();
        $adapter = new MidtransAdapter();
        $service->registerGateway($adapter);

        $result = $service->processCharge('midtrans', ['amount' => 100000]);

        $this->assertTrue($result['success']);
        $this->assertEquals('midtrans', $result['gateway']);
    }

    public function testProcessChargeWithUnsupportedGateway() {
        $service = new PaymentService();
        $result = $service->processCharge('unknown', ['amount' => 100000]);

        $this->assertFalse($result['success']);
    }
}
