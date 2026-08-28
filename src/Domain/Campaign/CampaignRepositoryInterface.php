<?php
namespace DonasiYuk\Domain\Campaign;

interface CampaignRepositoryInterface {
    public function findById(int $id): ?object;
    public function findBySlug(string $slug): ?object;
    public function getActiveCampaigns(int $limit = 10, int $offset = 0): array;
}
