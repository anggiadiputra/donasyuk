<?php
namespace DonasiYuk\Adapters\Repository;

use DonasiYuk\Domain\Donation\DonationRepositoryInterface;

class WpDonationRepository implements DonationRepositoryInterface {
    public function findById(int $id): ?object {
        global $wpdb;
        $table = $wpdb->prefix . 'dyk_donate';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id));
        return $row ?: null;
    }

    public function findByInvoice(string $invoiceId): ?object {
        global $wpdb;
        $table = $wpdb->prefix . 'dyk_donate';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE invoice_id = %s", $invoiceId));
        return $row ?: null;
    }

    public function create(array $data): int {
        global $wpdb;
        if (!isset($wpdb) || !$wpdb) {
            return 9999;
        }
        $table = $wpdb->prefix . 'dyk_donate';
        $inserted = $wpdb->insert($table, $data);
        return $inserted ? (int) $wpdb->insert_id : 0;
    }

    public function updateStatus(int $id, string $status): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'dyk_donate';
        $updated = $wpdb->update($table, ['status' => $status], ['id' => $id]);
        return $updated !== false;
    }
}
