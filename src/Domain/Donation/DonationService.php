<?php
namespace DonasiYuk\Domain\Donation;

class DonationService implements DonationServiceInterface {
    private DonationRepositoryInterface $repository;

    public function __construct(DonationRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function createDonation(array $data): array {
        $id = $this->repository->create($data);
        return [
            'id' => $id,
            'status' => 'pending',
            'created' => true,
        ];
    }

    public function markAsPaid(string $invoiceId, string $trxId = ''): bool {
        $donation = $this->repository->findByInvoice($invoiceId);
        if (!$donation) {
            return false;
        }
        return $this->repository->updateStatus($donation->id, 'paid');
    }
}
