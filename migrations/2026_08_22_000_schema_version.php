<?php
if (!defined('ABSPATH')) {
    exit;
}

function donasiyuk_migrate_schema_version_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'dyk_schema_version';

    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
        version int(11) NOT NULL,
        applied_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (version)
    ) {$charset_collate};";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
