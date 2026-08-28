<?php
namespace DonasiYuk\Domain\MobileSdk;

interface MobileSdkServiceInterface {
    public function authenticateClient(string $apiKey): array;
    public function getCampaignFeed(int $page = 1, int $perPage = 10): array;
    public function initializeMobileCheckout(int $campaignId, array $donorDetails): array;
}
