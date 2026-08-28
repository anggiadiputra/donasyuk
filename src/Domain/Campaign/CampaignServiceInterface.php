<?php
namespace DonasiYuk\Domain\Campaign;

interface CampaignServiceInterface {
    public function getCampaignDetails(int $id): ?object;
    public function calculateProgress(float $current, float $target): float;
}
