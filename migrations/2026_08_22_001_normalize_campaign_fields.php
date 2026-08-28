<?php
if (!defined('ABSPATH')) {
    exit;
}

function donasiyuk_migrate_normalize_campaign_fields() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // 1. dyk_campaign_payment_method
    $t_payment = $wpdb->prefix . 'dyk_campaign_payment_method';
    $sql_payment = "CREATE TABLE IF NOT EXISTS {$t_payment} (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        campaign_id bigint(20) NOT NULL,
        payment_method_id varchar(50) NOT NULL,
        method_type varchar(50) DEFAULT 'instant' NOT NULL,
        status tinyint(1) DEFAULT 1 NOT NULL,
        config_json text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY campaign_id (campaign_id)
    ) {$charset_collate};";
    dbDelta($sql_payment);

    // 2. dyk_campaign_bank_account
    $t_bank = $wpdb->prefix . 'dyk_campaign_bank_account';
    $sql_bank = "CREATE TABLE IF NOT EXISTS {$t_bank} (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        campaign_id bigint(20) NOT NULL,
        bank_code varchar(20) NOT NULL,
        account_number varchar(100) NOT NULL,
        account_name varchar(150) NOT NULL,
        qris_image text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY campaign_id (campaign_id)
    ) {$charset_collate};";
    dbDelta($sql_bank);

    // 3. dyk_campaign_cs
    $t_cs = $wpdb->prefix . 'dyk_campaign_cs';
    $sql_cs = "CREATE TABLE IF NOT EXISTS {$t_cs} (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        campaign_id bigint(20) NOT NULL,
        user_id bigint(20) NOT NULL,
        priority int(11) DEFAULT 0 NOT NULL,
        weight int(11) DEFAULT 1 NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY campaign_id (campaign_id)
    ) {$charset_collate};";
    dbDelta($sql_cs);

    // 4. dyk_campaign_form_text
    $t_form = $wpdb->prefix . 'dyk_campaign_form_text';
    $sql_form = "CREATE TABLE IF NOT EXISTS {$t_form} (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        campaign_id bigint(20) NOT NULL,
        locale varchar(10) DEFAULT 'id' NOT NULL,
        meta_key varchar(100) NOT NULL,
        meta_value text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY campaign_id (campaign_id)
    ) {$charset_collate};";
    dbDelta($sql_form);

    // 5. dyk_campaign_unique_number
    $t_unique = $wpdb->prefix . 'dyk_campaign_unique_number';
    $sql_unique = "CREATE TABLE IF NOT EXISTS {$t_unique} (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        campaign_id bigint(20) NOT NULL,
        mode varchar(20) DEFAULT 'random' NOT NULL,
        fixed_value int(11) DEFAULT 0 NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY campaign_id (campaign_id)
    ) {$charset_collate};";
    dbDelta($sql_unique);

    // Record migration in dyk_schema_version
    $ver_table = $wpdb->prefix . 'dyk_schema_version';
    $wpdb->replace($ver_table, [
        'version'    => 3,
        'applied_at' => current_time('mysql'),
    ]);
}
