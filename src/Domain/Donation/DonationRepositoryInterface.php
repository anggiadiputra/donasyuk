<?php
namespace DonasiYuk\Domain\Donation;

interface DonationRepositoryInterface {
    public function findById(int $id): ?object;
    public function findByInvoice(string $invoiceId): ?object;
    public function create(array $data): int;
    public function updateStatus(int $id, string $status): bool;
}
