<?php
namespace DonasiYuk\Domain\Recurring;

interface RecurringDonationServiceInterface {
    public function createSubscription(int $campaignId, string $donorEmail, float $amount, string $interval = 'monthly'): array;
    public function cancelSubscription(string $subscriptionId): bool;
    public function processRecurringCharges(): array;
    public function calculateMatchedDonation(float $donationAmount, float $matchMultiplier = 1.0, float $maxMatch = 1000000.0): float;
}
