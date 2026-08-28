<?php
namespace DonasiYuk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use DonasiYuk\Domain\Donation\DonationService;
use DonasiYuk\Domain\Donation\DonationRepositoryInterface;

class DonationServiceTest extends TestCase {
    public function testCreateDonationReturnsPendingStatus() {
        $repo = $this->createMock(DonationRepositoryInterface::class);
        $repo->method('create')->willReturn(101);

        $service = new DonationService($repo);
        $result = $service->createDonation(['nominal' => 50000]);

        $this->assertEquals(101, $result['id']);
        $this->assertEquals('pending', $result['status']);
        $this->assertTrue($result['created']);
    }
}
