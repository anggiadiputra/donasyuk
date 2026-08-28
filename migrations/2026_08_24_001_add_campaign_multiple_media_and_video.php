<?php
if (!defined('ABSPATH')) {
    exit;
}

function donasiyuk_migrate_add_campaign_multiple_media_and_video() {
    global $wpdb;
    $table_campaign = $wpdb->prefix . 'dyk_campaign';

    // Check and add media_type
    $col_media_type = $wpdb->get_results("SHOW COLUMNS FROM `{$table_campaign}` LIKE 'media_type'");
    if (empty($col_media_type)) {
        $wpdb->query("ALTER TABLE `{$table_campaign}` ADD COLUMN `media_type` VARCHAR(20) NOT NULL DEFAULT 'image' AFTER `image_url`");
    }

    // Check and add gallery_urls
    $col_gallery_urls = $wpdb->get_results("SHOW COLUMNS FROM `{$table_campaign}` LIKE 'gallery_urls'");
    if (empty($col_gallery_urls)) {
        $wpdb->query("ALTER TABLE `{$table_campaign}` ADD COLUMN `gallery_urls` LONGTEXT DEFAULT NULL AFTER `media_type`");
    }

    // Check and add video_url
    $col_video_url = $wpdb->get_results("SHOW COLUMNS FROM `{$table_campaign}` LIKE 'video_url'");
    if (empty($col_video_url)) {
        $wpdb->query("ALTER TABLE `{$table_campaign}` ADD COLUMN `video_url` TEXT DEFAULT NULL AFTER `gallery_urls`");
    }

    // Record migration in dyk_schema_version if table exists
    $ver_table = $wpdb->prefix . 'dyk_schema_version';
    $check_ver_table = $wpdb->get_var("SHOW TABLES LIKE '{$ver_table}'");
    if ($check_ver_table) {
        $wpdb->replace($ver_table, [
            'version'    => 4,
            'applied_at' => current_time('mysql'),
        ]);
    }
}
