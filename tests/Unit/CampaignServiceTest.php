<?php
namespace DonasiYuk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use DonasiYuk\Domain\Campaign\CampaignService;
use DonasiYuk\Domain\Campaign\CampaignRepositoryInterface;

class CampaignServiceTest extends TestCase {
    public function testCalculateProgressReturnsCorrectPercentage() {
        $repo = $this->createMock(CampaignRepositoryInterface::class);
        $service = new CampaignService($repo);

        $this->assertEquals(50.0, $service->calculateProgress(500000, 1000000));
        $this->assertEquals(100.0, $service->calculateProgress(1500000, 1000000));
        $this->assertEquals(0.0, $service->calculateProgress(500000, 0));
    }
}
