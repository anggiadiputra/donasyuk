<?php
/**
 * Migration: Rename DB tables dja_* → dyk_*
 *
 * Runs on plugin activation when migrating from DonasiAja v2.x to DonasiYuk 3.0.
 * Safe to run multiple times (idempotent).
 *
 * @package DonasiYuk
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

function donasiyuk_migrate_rename_tables_dja_to_dyk() {
	global $wpdb;

	$tables = array(
		'dja_settings',
		'dja_campaign',
		'dja_campaign_update',
		'dja_category',
		'dja_donate',
		'dja_aff_code',
		'dja_love',
		'dja_payment_list',
		'dja_register',
		'dja_shortcode',
		'dja_users',
		'dja_user_logs',
		'dja_user_type',
		'dja_verification_details',
		'dja_verification_status',
		'dja_payment_log',
		'dja_password_reset',
		'dja_password_reset_log',
		'dja_aff_click',
		'dja_aff_submit',
		'dja_aff_payout',
		'dja_payment_callback',
		'dja_wilayah_malaysia',
		'dja_donate_trash',
		'dja_group_list',
		'dja_group_data',
		'dja_custom_followup_scheduler',
		'dja_blocked_ip',
		'dja_blocked_whatsapp',
	);

	$migrated = 0;
	$skipped = 0;
	$errors  = array();

	foreach ( $tables as $t ) {
		$old = $wpdb->prefix . $t;
		$new = $wpdb->prefix . str_replace( 'dja_', 'dyk_', $t );

		// Skip if new already exists (idempotent)
		$new_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $new ) );
		if ( $new_exists ) {
			++$skipped;
			continue;
		}

		// Skip if old doesn't exist
		$old_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $old ) );
		if ( ! $old_exists ) {
			++$skipped;
			continue;
		}

		// RENAME TABLE (MySQL/MariaDB native, atomic, fast)
		$sql = "RENAME TABLE `{$old}` TO `{$new}`";
		$result = $wpdb->query( $sql );

		if ( $result === false ) {
			$errors[] = sprintf( 'Failed: %s → %s (%s)', $old, $new, $wpdb->last_error );
		} else {
			++$migrated;
		}
	}

	// Normalize theme_color row: original DonasiAja default was {"color":["","",""]}
	// (3 entries) but the settings page reads index [3]. Pad to 4 if missing so
	// the page does not emit PHP warnings on first load after migration.
	$settings_table = $wpdb->prefix . 'dyk_settings';
	$tc_row = $wpdb->get_var( $wpdb->prepare(
		"SELECT data FROM {$settings_table} WHERE type = %s LIMIT 1",
		'theme_color'
	) );
	if ( $tc_row ) {
		$decoded = json_decode( $tc_row, true );
		if ( is_array( $decoded ) && isset( $decoded['color'] ) && is_array( $decoded['color'] ) && count( $decoded['color'] ) < 4 ) {
			while ( count( $decoded['color'] ) < 4 ) {
				$decoded['color'][] = '';
			}
			$wpdb->update(
				$settings_table,
				array( 'data' => wp_json_encode( $decoded ) ),
				array( 'type' => 'theme_color' ),
				array( '%s' ),
				array( '%s' )
			);
		}
	}

	// Log to WP option for traceability
	update_option( 'donasiyuk_last_migration', array(
		'migration'  => 'rename_tables_dja_to_dyk',
		'run_at'     => current_time( 'mysql' ),
		'migrated'   => $migrated,
		'skipped'    => $skipped,
		'errors'     => $errors,
		'wp_version' => $GLOBALS['wp_version'],
		'php_version' => PHP_VERSION,
	) );

	return array(
		'migrated' => $migrated,
		'skipped'  => $skipped,
		'errors'   => $errors,
	);
}
