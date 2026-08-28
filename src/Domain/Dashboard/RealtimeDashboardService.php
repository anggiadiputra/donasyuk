<?php
namespace DonasiYuk\Domain\Dashboard;

class RealtimeDashboardService implements RealtimeDashboardInterface {
    public function getMetrics(): array {
        global $wpdb;
        $table_donations = $wpdb->prefix . 'dyk_donations';
        $table_campaigns = $wpdb->prefix . 'dyk_campaigns';

        $totalDonations = 0;
        $totalRaised = 0.0;
        $activeCampaigns = 0;

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_donations'" ) === $table_donations ) {
            $stats = $wpdb->get_row( "SELECT COUNT(id) as cnt, COALESCE(SUM(amount), 0) as total FROM {$table_donations} WHERE status = 'paid'", ARRAY_A );
            if ($stats) {
                $totalDonations = (int) $stats['cnt'];
                $totalRaised = (float) $stats['total'];
            }
        }

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_campaigns'" ) === $table_campaigns ) {
            $activeCampaigns = (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table_campaigns} WHERE status = 'active'" );
        }

        return [
            'total_donations' => $totalDonations,
            'total_raised' => $totalRaised,
            'active_campaigns' => $activeCampaigns,
            'timestamp' => time()
        ];
    }

    public function getRecentDonations(int $limit = 10): array {
        global $wpdb;
        $table_donations = $wpdb->prefix . 'dyk_donations';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_donations'" ) !== $table_donations ) {
            return [];
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, donor_name, amount, campaign_id, created_at, status
                 FROM {$table_donations}
                 ORDER BY id DESC
                 LIMIT %d",
                $limit
            ),
            ARRAY_A
        );

        return $results ? $results : [];
    }

    public function streamUpdates(): void {
        if (headers_sent()) {
            return;
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $data = json_encode([
            'metrics' => $this->getMetrics(),
            'recent' => $this->getRecentDonations(5)
        ]);

        echo "data: {$data}\n\n";
        flush();
    }
}
