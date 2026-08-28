<?php
namespace DonasiYuk\Domain\Recurring;

class RecurringDonationService implements RecurringDonationServiceInterface {
    public function createSubscription(int $campaignId, string $donorEmail, float $amount, string $interval = 'monthly'): array {
        $subscriptionId = 'SUB-' . strtoupper(substr(md5(uniqid((string) time(), true)), 0, 10));

        $cleanEmail = function_exists('sanitize_email') ? sanitize_email($donorEmail) : filter_var($donorEmail, FILTER_SANITIZE_EMAIL);

        return [
            'subscription_id' => $subscriptionId,
            'campaign_id'     => $campaignId,
            'donor_email'     => $cleanEmail,
            'amount'          => $amount,
            'interval'        => $interval,
            'status'          => 'active',
            'created_at'      => date('Y-m-d H:i:s'),
        ];
    }

    public function cancelSubscription(string $subscriptionId): bool {
        // Stub for provider API cancellation (Midtrans/Xendit subscription)
        return true;
    }

    public function processRecurringCharges(): array {
        // Stub for scheduled cron processing of due recurring donations
        return [
            'processed' => 0,
            'success'   => 0,
            'failed'    => 0,
        ];
    }

    public function calculateMatchedDonation(float $donationAmount, float $matchMultiplier = 1.0, float $maxMatch = 1000000.0): float {
        $matched = $donationAmount * $matchMultiplier;
        return min($matched, $maxMatch);
    }
}
