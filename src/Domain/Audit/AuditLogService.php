<?php
namespace DonasiYuk\Domain\Audit;

class AuditLogService implements AuditLogServiceInterface {
    public function log(string $action, array $context = [], string $level = 'info'): bool {
        global $wpdb;
        $table_audit = $wpdb->prefix . 'dyk_audit_logs';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_audit'" ) !== $table_audit ) {
            // Log fallback to error_log
            error_log(sprintf('[DonasiYuk Audit] [%s] %s: %s', strtoupper($level), $action, json_encode($context)));
            return true;
        }

        $inserted = $wpdb->insert(
            $table_audit,
            [
                'action'     => sanitize_text_field($action),
                'level'      => sanitize_text_field($level),
                'context'    => json_encode($context),
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [ '%s', '%s', '%s', '%s' ]
        );

        return $inserted !== false;
    }

    public function getLogs(int $limit = 50): array {
        global $wpdb;
        $table_audit = $wpdb->prefix . 'dyk_audit_logs';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_audit'" ) !== $table_audit ) {
            return [];
        }

        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table_audit} ORDER BY id DESC LIMIT %d", $limit),
            ARRAY_A
        );

        return $results ? $results : [];
    }
}
