<?php
if (!defined('ABSPATH')) {
    exit;
}

function donasiyuk_migrate_add_cidr_and_rate_limit() {
    global $wpdb;

    $table_ip       = $wpdb->prefix . 'dyk_blocked_ip';
    $table_settings = $wpdb->prefix . 'dyk_settings';

    // 1) Add cidr column to dyk_blocked_ip if missing.
    $col_cidr = $wpdb->get_results("SHOW COLUMNS FROM `{$table_ip}` LIKE 'cidr'");
    if (empty($col_cidr)) {
        $wpdb->query(
            "ALTER TABLE `{$table_ip}` ADD COLUMN `cidr` VARCHAR(64) DEFAULT NULL COMMENT 'CIDR range, optional'"
        );
    }

    // 2) Seed default rate-limit settings (only insert if missing — never overwrite).
    $default_settings = array(
        'rate_limit_front_max'    => '60',   // 60 page loads per IP per window (front pages)
        'rate_limit_front_window' => '60',   // window in seconds (1 minute)
        'rate_limit_form_max'     => '10',   // 10 form submits per IP per window
        'rate_limit_form_window'  => '60',   // window in seconds
    );
    foreach ( $default_settings as $key => $val ) {
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM `{$table_settings}` WHERE `type` = %s LIMIT 1",
                $key
            )
        );
        if ( ! $exists ) {
            $wpdb->insert(
                $table_settings,
                array(
                    'type' => $key,
                    'data' => $val,
                )
            );
        }
    }

    // 3) Record migration.
    $ver_table = $wpdb->prefix . 'dyk_schema_version';
    $check_ver_table = $wpdb->get_var( "SHOW TABLES LIKE '{$ver_table}'" );
    if ( $check_ver_table ) {
        $wpdb->replace(
            $ver_table,
            array(
                'version'    => 6,
                'applied_at' => current_time( 'mysql' ),
            )
        );
    }
}
