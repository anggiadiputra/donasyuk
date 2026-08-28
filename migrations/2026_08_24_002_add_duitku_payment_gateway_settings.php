<?php
if (!defined('ABSPATH')) {
    exit;
}

function donasiyuk_migrate_add_duitku_payment_gateway_settings() {
    global $wpdb;
    $table_settings = $wpdb->prefix . 'dyk_settings';

    $default_settings = [
        'duitku_mode'             => '0',
        'duitku_merchant'         => '',
        'duitku_apikey'           => '',
        'duitku_merchant_sandbox' => '',
        'duitku_apikey_sandbox'   => '',
        'duitku_expiry_period'    => '1440',
        'duitku_qris_type'        => 'SP'
    ];

    foreach ($default_settings as $key => $val) {
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM `{$table_settings}` WHERE `type` = %s LIMIT 1", $key));
        if (!$exists) {
            $wpdb->insert($table_settings, [
                'type' => $key,
                'data' => $val
            ]);
        }
    }

    // Record migration in dyk_schema_version if table exists
    $ver_table = $wpdb->prefix . 'dyk_schema_version';
    $check_ver_table = $wpdb->get_var("SHOW TABLES LIKE '{$ver_table}'");
    if ($check_ver_table) {
        $wpdb->replace($ver_table, [
            'version'    => 5,
            'applied_at' => current_time('mysql'),
        ]);
    }
}
