<?php
namespace DonasiYuk\Tests\Unit;

use PHPUnit\Framework\TestCase;
use DonasiYuk\Domain\Fundraising\FundraiserService;
use DonasiYuk\Domain\Dashboard\RealtimeDashboardService;
use DonasiYuk\Domain\Calculator\CalculatorService;

class MilestonesM11M12M13Test extends TestCase {
    public function testCalculatorServiceNisabAndZakat() {
        $calc = new CalculatorService();
        $resMaal = $calc->calculateZakatMaal(150000000.0, 1300000.0);
        $this->assertTrue($resMaal['is_wajib']);
        $this->assertEquals(3750000.0, $resMaal['zakat_amount']);

        $resProfesi = $calc->calculateZakatProfesi(15000000.0, 2000000.0, 1300000.0);
        $this->assertTrue($resProfesi['is_wajib']);
        $this->assertEquals(325000.0, $resProfesi['zakat_amount']);

        $resQurban = $calc->calculateQurbanAnimal(15, 'sapi');
        $this->assertEquals(3, $resQurban['animals_needed']);
    }

    public function testFundraiserServiceDefaultStats() {
        $service = new FundraiserService();
        $stats = $service->getFundraiserStats(999);
        $this->assertEquals(0, $stats['total_referrals']);
    }

    public function testRealtimeDashboardDefaultMetrics() {
        $dashboard = new RealtimeDashboardService();
        $metrics = $dashboard->getMetrics();
        $this->assertArrayHasKey('total_donations', $metrics);
        $this->assertArrayHasKey('total_raised', $metrics);
    }
}
