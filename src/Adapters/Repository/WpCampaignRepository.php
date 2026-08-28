<?php
namespace DonasiYuk\Adapters\Repository;

use DonasiYuk\Domain\Campaign\CampaignRepositoryInterface;

class WpCampaignRepository implements CampaignRepositoryInterface {
    public function findById(int $id): ?object {
        global $wpdb;
        $table = $wpdb->prefix . 'dyk_campaign';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id));
        return $row ?: null;
    }

    public function findBySlug(string $slug): ?object {
        global $wpdb;
        $table = $wpdb->prefix . 'dyk_campaign';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE slug = %s", $slug));
        return $row ?: null;
    }

    public function getActiveCampaigns(int $limit = 10, int $offset = 0): array {
        global $wpdb;
        $table = $wpdb->prefix . 'dyk_campaign';
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE payment_status = 1 ORDER BY id DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        )) ?: [];
    }
}
