<?php
namespace DonasiYuk\Domain\Donation;

interface DonationServiceInterface {
    public function createDonation(array $data): array;
    public function markAsPaid(string $invoiceId, string $trxId = ''): bool;
}
