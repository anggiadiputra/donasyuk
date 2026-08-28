<?php
namespace DonasiYuk\Domain\Fundraising;

interface FundraiserServiceInterface {
    public function getLeaderboard(int $campaignId, int $limit = 10): array;
    public function recordReferral(string $referralCode, int $donationId): bool;
    public function getFundraiserStats(int $userId): array;
}
