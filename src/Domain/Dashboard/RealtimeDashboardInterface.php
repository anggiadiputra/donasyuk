<?php
namespace DonasiYuk\Domain\Dashboard;

interface RealtimeDashboardInterface {
    public function getMetrics(): array;
    public function getRecentDonations(int $limit = 10): array;
    public function streamUpdates(): void;
}
