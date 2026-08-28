<?php
/**
 * DonasiYuk — Webhook Idempotency Helper
 *
 * Provides a single entry point for payment gateway webhooks to ensure that
 * the same webhook payload is processed AT MOST ONCE, even under retries,
 * concurrent requests, or replication lag.
 *
 * Pattern: atomic UPDATE with rows_affected check. The UPDATE only succeeds
 * if the row is currently in status=0 (pending). If it returns >0, this
 * caller is the unique processor; side-effects (WA, email, metapixel,
 * receipt, etc.) should be fired AFTER this call returns true. If it
 * returns 0, another concurrent webhook already processed (or failed);
 * caller MUST exit silently with a 2xx so the gateway stops retrying.
 *
 * Usage:
 *   require_once ROOTDIR_DYK . 'library/dyk_webhook_idempotency.php';
 *
 *   $ok = dyk_webhook_claim_donation( $table_name2, $payment_trx_id, 'ipaymu', $extra_where );
 *   if ( ! $ok ) {
 *       // Already processed; ack the gateway and stop.
 *       status_header(200);
 *       exit;
 *   }
 *   // We are the unique processor; fire side-effects here.
 *
 * @package DonasiYuk
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Atomically claim a donation row by payment_trx_id (and optional extra WHERE).
 *
 * Returns true if this caller is the unique processor (UPDATE transitioned 1 row);
 * false if the row was already processed or does not exist.
 *
 * @param string $table_name   Fully-qualified table name (with WP prefix).
 * @param string $payment_trx_id  Gateway transaction/reference ID.
 * @param string $process_by   Tag for audit ("ipaymu", "tripay", "midtrans", "remitcepat", "flip").
 * @param array  $extra_where  Optional extra WHERE pairs as ['col' => 'value']. All are %s.
 * @return bool
 */
function dyk_webhook_claim_donation( $table_name, $payment_trx_id, $process_by, $extra_where = array() ) {
	global $wpdb;

	$set_parts   = array( 'status = 1', 'payment_at = %s', 'process_by = %s' );
	$set_values  = array( current_time( 'mysql' ), $process_by );

	$where_parts = array( 'payment_trx_id = %s', 'status = 0' );
	$where_values = array( $payment_trx_id );

	foreach ( $extra_where as $col => $val ) {
		$where_parts[] = "{$col} = %s";
		$where_values[] = $val;
	}

	$sql = sprintf(
		'UPDATE %s SET %s WHERE %s',
		$table_name,
		implode( ', ', $set_parts ),
		implode( ' AND ', $where_parts )
	);

	$params = array_merge( $set_values, $where_values );
	$claimed = $wpdb->query( $wpdb->prepare( $sql, $params ) );

	return ( $claimed > 0 );
}

/**
 * Same as dyk_webhook_claim_donation() but returns the donation row when
 * claim succeeded, or null on failure. Saves the caller a follow-up SELECT.
 *
 * @return object|null
 */
function dyk_webhook_claim_and_get( $table_name, $payment_trx_id, $process_by, $extra_where = array() ) {
	global $wpdb;

	if ( ! dyk_webhook_claim_donation( $table_name, $payment_trx_id, $process_by, $extra_where ) ) {
		return null;
	}

	$where_parts = array( 'payment_trx_id = %s' );
	$where_values = array( $payment_trx_id );
	foreach ( $extra_where as $col => $val ) {
		$where_parts[] = "{$col} = %s";
		$where_values[] = $val;
	}
	$sql = sprintf(
		'SELECT * FROM %s WHERE %s LIMIT 1',
		$table_name,
		implode( ' AND ', $where_parts )
	);
	return $wpdb->get_row( $wpdb->prepare( $sql, $where_values ) );
}

/**
 * Atomically claim a donation row by invoice_id.
 *
 * @param string $table_name   Fully-qualified table name (with WP prefix).
 * @param string $invoice_id   Merchant invoice ID.
 * @param string $process_by   Tag for audit ("duitku", "tripay", etc.).
 * @param array  $extra_where  Optional extra WHERE pairs as ['col' => 'value'].
 * @return bool
 */
function dyk_webhook_claim_by_invoice( $table_name, $invoice_id, $process_by, $extra_where = array() ) {
	global $wpdb;

	$set_parts   = array( 'status = 1', 'payment_at = %s', 'process_by = %s' );
	$set_values  = array( current_time( 'mysql' ), $process_by );

	$where_parts = array( 'invoice_id = %s', 'status = 0' );
	$where_values = array( $invoice_id );

	foreach ( $extra_where as $col => $val ) {
		$where_parts[] = "{$col} = %s";
		$where_values[] = $val;
	}

	$sql = sprintf(
		'UPDATE %s SET %s WHERE %s',
		$table_name,
		implode( ', ', $set_parts ),
		implode( ' AND ', $where_parts )
	);

	$params = array_merge( $set_values, $where_values );
	$claimed = $wpdb->query( $wpdb->prepare( $sql, $params ) );

	return ( $claimed > 0 );
}

/**
 * Same as dyk_webhook_claim_by_invoice() but returns the donation row when
 * claim succeeded, or null on failure.
 *
 * @return object|null
 */
function dyk_webhook_claim_and_get_by_invoice( $table_name, $invoice_id, $process_by, $extra_where = array() ) {
	global $wpdb;

	if ( ! dyk_webhook_claim_by_invoice( $table_name, $invoice_id, $process_by, $extra_where ) ) {
		return null;
	}

	$where_parts = array( 'invoice_id = %s' );
	$where_values = array( $invoice_id );
	foreach ( $extra_where as $col => $val ) {
		$where_parts[] = "{$col} = %s";
		$where_values[] = $val;
	}
	$sql = sprintf(
		'SELECT * FROM %s WHERE %s LIMIT 1',
		$table_name,
		implode( ' AND ', $where_parts )
	);
	return $wpdb->get_row( $wpdb->prepare( $sql, $where_values ) );
}
