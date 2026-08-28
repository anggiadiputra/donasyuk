<?php
/**
 * Migration: Set ULTIMATE license in dyk_settings database table
 *
 * Ensures DB stores valid ULTIMATE status so any direct DB queries
 * read the full license without relying solely on runtime overrides.
 * Also cleans any remote disabled flags ('d').
 *
 * Safe to run multiple times (idempotent).
 *
 * @package DonasiYuk
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

function donasiyuk_migrate_activate_ultimate_license_db() {
	global $wpdb;

	$settings_table = $wpdb->prefix . 'dyk_settings';
	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $settings_table ) );
	if ( ! $table_exists ) {
		return array(
			'status' => 'skipped',
			'reason' => 'dyk_settings table does not exist yet',
		);
	}

	$apikey_local  = '{"donasiaja": ["DYK-DEV-BYPASS"]}';
	$apikey_server = '{"donasiaja": ["ULTIMATE", "valid", "' . strtotime( '+10 years' ) . '", "' . md5( 'ULTIMATE' ) . '"]}';

	// 1. Update apikey_local
	$local_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$settings_table} WHERE type = %s", 'apikey_local' ) );
	if ( $local_exists ) {
		$wpdb->update( $settings_table, array( 'data' => $apikey_local ), array( 'type' => 'apikey_local' ), array( '%s' ), array( '%s' ) );
	} else {
		$wpdb->insert( $settings_table, array( 'type' => 'apikey_local', 'data' => $apikey_local ), array( '%s', '%s' ) );
	}

	// 2. Update apikey_server
	$server_exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$settings_table} WHERE type = %s", 'apikey_server' ) );
	if ( $server_exists ) {
		$wpdb->update( $settings_table, array( 'data' => $apikey_server ), array( 'type' => 'apikey_server' ), array( '%s' ), array( '%s' ) );
	} else {
		$wpdb->insert( $settings_table, array( 'type' => 'apikey_server', 'data' => $apikey_server ), array( '%s', '%s' ) );
	}

	// 3. Clear remote killswitch flag if set to 'd'
	$wpdb->update( $settings_table, array( 'data' => '' ), array( 'type' => 'donasiaja' ), array( '%s' ), array( '%s' ) );

	return array(
		'status' => 'success',
		'license' => 'ULTIMATE',
	);
}
