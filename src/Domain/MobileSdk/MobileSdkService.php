<?php
namespace DonasiYuk\Domain\MobileSdk;

use DonasiYuk\Domain\Campaign\CampaignServiceInterface;
use DonasiYuk\Domain\Donation\DonationServiceInterface;

class MobileSdkService implements MobileSdkServiceInterface {
    private ?CampaignServiceInterface $campaignService;
    private ?DonationServiceInterface $donationService;

    public function __construct(?CampaignServiceInterface $campaignService = null, ?DonationServiceInterface $donationService = null) {
        $this->campaignService = $campaignService;
        $this->donationService = $donationService;
    }

    public function authenticateClient(string $apiKey): array {
        if (empty($apiKey)) {
            return ['authenticated' => false, 'error' => 'API Key required'];
        }

        return [
            'authenticated' => true,
            'client_id'     => 'MOB-' . substr(md5($apiKey), 0, 8),
            'environment'   => 'production',
        ];
    }

    public function getCampaignFeed(int $page = 1, int $perPage = 10): array {
        if ($this->campaignService) {
            $campaigns = $this->campaignService->getActiveCampaigns();
            $offset = ($page - 1) * $perPage;
            $items = array_slice($campaigns, $offset, $perPage);
            return [
                'page'     => $page,
                'per_page' => $perPage,
                'items'    => $items,
            ];
        }

        return [
            'page'     => $page,
            'per_page' => $perPage,
            'items'    => [],
        ];
    }

    public function initializeMobileCheckout(int $campaignId, array $donorDetails): array {
        $amount = (float)($donorDetails['amount'] ?? 0);
        $name   = $donorDetails['name'] ?? 'Mobile Donor';
        $email  = $donorDetails['email'] ?? '';

        if ($this->donationService) {
            return $this->donationService->createDonation([
                'campaign_id' => $campaignId,
                'donor_name'  => $name,
                'donor_email' => $email,
                'amount'      => $amount,
                'channel'     => 'mobile_sdk',
            ]);
        }

        return [
            'success'        => true,
            'transaction_id' => 'MOB-TRX-' . time(),
            'checkout_url'   => 'https://donasiyuk.local/checkout/mobile?trx=' . time(),
        ];
    }
}
