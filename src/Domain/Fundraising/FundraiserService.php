<?php
namespace DonasiYuk\Domain\Fundraising;

class FundraiserService implements FundraiserServiceInterface {
    public function getLeaderboard(int $campaignId, int $limit = 10): array {
        global $wpdb;
        $table_donations = $wpdb->prefix . 'dyk_donations';
        
        // Return top fundraisers by total donation amount or referral count
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_donations'" ) !== $table_donations ) {
            return [];
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT referral_code, COUNT(id) as total_referrals, SUM(amount) as total_raised
                 FROM {$table_donations}
                 WHERE campaign_id = %d AND status = 'paid' AND referral_code IS NOT NULL AND referral_code != ''
                 GROUP BY referral_code
                 ORDER BY total_raised DESC
                 LIMIT %d",
                $campaignId,
                $limit
            ),
            ARRAY_A
        );

        return $results ? $results : [];
    }

    public function recordReferral(string $referralCode, int $donationId): bool {
        global $wpdb;
        $table_donations = $wpdb->prefix . 'dyk_donations';
        
        $updated = $wpdb->update(
            $table_donations,
            [ 'referral_code' => sanitize_text_field($referralCode) ],
            [ 'id' => $donationId ],
            [ '%s' ],
            [ '%d' ]
        );

        return $updated !== false;
    }

    public function getFundraiserStats(int $userId): array {
        global $wpdb;
        $table_donations = $wpdb->prefix . 'dyk_donations';
        $user_code = 'REF' . $userId;

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_donations'" ) !== $table_donations ) {
            return [ 'total_referrals' => 0, 'total_raised' => 0 ];
        }

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT COUNT(id) as total_referrals, COALESCE(SUM(amount), 0) as total_raised
                 FROM {$table_donations}
                 WHERE referral_code = %s AND status = 'paid'",
                $user_code
            ),
            ARRAY_A
        );

        return $row ? [
            'total_referrals' => (int) $row['total_referrals'],
            'total_raised' => (float) $row['total_raised'],
        ] : [ 'total_referrals' => 0, 'total_raised' => 0 ];
    }
}
