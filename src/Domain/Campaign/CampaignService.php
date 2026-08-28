<?php
namespace DonasiYuk\Domain\Campaign;

class CampaignService implements CampaignServiceInterface {
    private CampaignRepositoryInterface $repository;

    public function __construct(CampaignRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function getCampaignDetails(int $id): ?object {
        return $this->repository->findById($id);
    }

    public function calculateProgress(float $current, float $target): float {
        if ($target <= 0) {
            return 0.0;
        }
        return min(100.0, round(($current / $target) * 100, 2));
    }
}
