<?php
/**
 * Migration: Replace legacy icon classes (FA/MDI/Dripicons/Glyphicon/Feather)
 *          with Lucide `data-lucide` attributes.
 *
 * Touches 10 source files in the plugin. Run from CLI:
 *   php migrations/2026_08_21_003_migrate_icons_to_lucide.php
 *
 * Idempotent: files that already migrated (have `data-lucide`) are skipped.
 *
 * @package DonasiYuk
 */

if ( ! defined( 'ABSPATH' ) && php_sapi_name() !== 'cli' ) {
	die;
}

// Mapping: token kedua (setelah prefix fas/far/fab/mdi/dripicons/etc) → Lucide icon name.
// Loop logic: saat scanning class tokens, jika token[1] (icon glyph) match key, ganti.
$lucide_map = array(
	// FontAwesome 5 solid
	'fa-minus'             => 'minus',
	'fa-arrows-alt'        => 'maximize-2',
	'fa-camera'            => 'camera',
	'fa-pen'               => 'pen-line',
	'fa-plus'              => 'plus',
	'fa-calendar-alt'      => 'calendar',
	'fa-user'              => 'user',
	'fa-trash-alt'         => 'trash-2',
	'fa-download'          => 'download',
	'fa-paperclip'         => 'paperclip',
	'fa-edit'              => 'pencil',
	'fa-cog'               => 'settings',
	// FontAwesome 5 regular (same glyph names)
	'fa-file-pdf'          => 'file-text',
	// FontAwesome 5 brands — handled specially (whatsapp → img svg)
	'fa-whatsapp'          => '__whatsapp_svg__',
	// FontAwesome 4 legacy (same names, no prefix glyph change)
	'fa-check-circle'      => 'circle-check',
	'fa-users'             => 'users',
	'fa-thumbs-up'         => 'thumbs-up',
	'fa-thumbs-down'       => 'thumbs-down',
	'fa-arrow-right'       => 'arrow-right',
	// Material Design Icons
	'mdi-trash-can'                   => 'trash-2',
	'mdi-close'                       => 'x',
	'mdi-plus'                        => 'plus',
	'mdi-pencil'                      => 'pencil',
	'mdi-content-copy'                => 'copy',
	'mdi-chevron-down'                => 'chevron-down',
	'mdi-chevron-left'                => 'chevron-left',
	'mdi-checkbox-marked-circle-outline' => 'circle-check',
	'mdi-plus-circle-outline'         => 'plus-circle',
	'mdi-file-table-outline'          => 'table',
	'mdi-file-document-outline'       => 'file-text',
	'mdi-file-document-box'           => 'file-box',
	'mdi-infinity'                    => 'infinity',
	'mdi-table-edit'                  => 'square-pen',
	'mdi-restore'                     => 'rotate-ccw',
	'mdi-printer'                     => 'printer',
	'mdi-link-variant'                => 'link',
	'mdi-brightness-1'                => 'circle',
	'mdi-bank'                        => 'landmark',
	'mdi-arrow-down-bold-box'         => 'arrow-down-square',
	'mdi-account'                     => 'user-round',
	// Dripicons (prefix is `dripicons-`, glyph = `user` etc.)
	'user'           => 'user',      // also matches dripicons-user
	'trophy'         => 'trophy',
	'heart'          => 'heart',
	'swap'           => 'arrow-right-left',
	'download'       => 'download',
	'document'       => 'file',
	'broadcast'      => 'radio',
	'web'            => 'globe',
	'user-group'     => 'users',
	'upload'         => 'upload',
	'stack'          => 'layers',
	'link'           => 'link',
	'graph-pie'      => 'pie-chart',
	'device-mobile'  => 'smartphone',
	'device-desktop' => 'monitor',
	// Bootstrap Glyphicon
	'glyphicon-remove' => 'x',
	// Feather
	'feather-users'    => 'users',
);

$plugin_root = dirname( __DIR__ );

$targets = array(
	$plugin_root . '/donasiyuk.php',
	$plugin_root . '/admin/f_donasiyuk_dashboard.php',
	$plugin_root . '/admin/f_donasiyuk_settings.php',
	$plugin_root . '/admin/f_donasiyuk_data_campaign.php',
	$plugin_root . '/admin/f_donasiyuk_data_shortcodes.php',
	$plugin_root . '/admin/f_donasiyuk_mydonate.php',
	$plugin_root . '/admin/f_donasiyuk_analytics.php',
	$plugin_root . '/admin/f_donasiyuk_data_fundraising.php',
	$plugin_root . '/admin/f_donasiyuk_data_members.php',
	$plugin_root . '/admin/f_donasiyuk_myprofile.php',
	$plugin_root . '/donasiyuk-typ.php',
	$plugin_root . '/donasiyuk-form.php',
	$plugin_root . '/donasiyuk-search.php',
);

$total_replaced = 0;

foreach ( $targets as $file ) {
	if ( ! file_exists( $file ) ) {
		echo "SKIP (missing): $file\n";
		continue;
	}

	$orig = file_get_contents( $file );
	$changed = $orig;

	// Idempotency check: if first 1KB already mentions data-lucide & no fa-/mdi-/dripicons- left,
	// assume migrated and skip.
	if ( strpos( $changed, 'data-lucide' ) !== false
		&& strpos( $changed, 'fas fa-' ) === false
		&& strpos( $changed, 'far fa-' ) === false
		&& strpos( $changed, 'fab fa-' ) === false
		&& strpos( $changed, 'mdi mdi-' ) === false
		&& strpos( $changed, 'dripicons-' ) === false
		&& strpos( $changed, 'glyphicon-' ) === false
		&& strpos( $changed, 'feather feather-' ) === false
	) {
		echo "SKIP (already migrated): $file\n";
		continue;
	}

	// Pattern: match <i|span ... class="..."> or class='...' (both quote styles).
	$pattern = '/(<[a-zA-Z]+)([^>]*?)\sclass=(["\'])([^"\']*)\3([^>]*>)/';

	// Pattern B: PHP-concatenated style like class='."'".'mdi mdi-trash-can mr-2'."'".'
	// Captures inner icon glyph directly without quote constraints.
	$pattern_php = '/class=' . chr( 0x27 ) . '\.' . chr( 0x27 ) . chr( 0x22 ) . chr( 0x27 ) . '\s*((?:fas|far|fab|mdi|dripicons|glyphicon|feather)\s+[a-z0-9-]+(?:\s+[a-z0-9-]+)*)\s*' . chr( 0x27 ) . chr( 0x22 ) . chr( 0x27 ) . '\.' . chr( 0x27 ) . '/';

	$replaced_in_file = 0;
	$changed2 = preg_replace_callback( $pattern, function ( $m ) use ( $lucide_map, &$replaced_in_file ) {
		$tag_open   = $m[1];
		$pre_attrs  = $m[2];
		$quote      = $m[3];
		$classes    = $m[4];
		$post_attrs = $m[5];

		$tokens   = preg_split( '/\s+/', trim( $classes ) );
		$icon_lucide = null;
		$kept_tokens = array();

		foreach ( $tokens as $idx => $tok ) {
			// Icon glyph = token ke-2 setelah prefix icon-font (fas/far/fab/fa/mdi/dripicons/glyphicon/feather).
			if ( $idx >= 1 && in_array( $tokens[ $idx - 1 ], array( 'fas', 'far', 'fab', 'fa', 'mdi', 'dripicons', 'glyphicon', 'feather' ), true ) ) {
				if ( isset( $lucide_map[ $tok ] ) ) {
					$icon_lucide = $lucide_map[ $tok ];
					$replaced_in_file++;
					// Skip this token; the prefix token already skipped via next iteration's else branch.
					continue;
				}
			}
			// Drop standalone prefix token (`fas`/`mdi`/etc).
			if ( preg_match( '/^(fas|far|fab|fa|mdi|dripicons|glyphicon|feather)$/', $tok ) ) {
				continue;
			}
			$kept_tokens[] = $tok;
		}

		// Whitelist-only: only proceed if matched icon was from our map.
		if ( $icon_lucide === null ) {
			return $m[0];
		}

		// Special case: fa-whatsapp → keep as SVG <img>.
		if ( $icon_lucide === '__whatsapp_svg__' ) {
			$new_class = trim( implode( ' ', $kept_tokens ) );
			$class_attr = $new_class === '' ? '' : ' class="' . $new_class . '"';
			// Try to detect plugin_dir context (admin file vs frontend). We use a relative-style fallback;
			// the caller is responsible for absolute path resolution. We use plugin root by detecting
			// if the file path mentions /admin/.
			$base = ( strpos( $m[0], 'admin' ) !== false ) ? '../' : '';
			return $tag_open . $pre_attrs . $class_attr . $post_attrs
				. '<img src="' . $base . 'assets/icons/whatsapp.svg" alt="WhatsApp" style="width:1em;height:1em;vertical-align:-0.125em;" />';
		}

		$new_class = trim( implode( ' ', $kept_tokens ) );
		$class_attr = $new_class === '' ? '' : ' class="' . $new_class . '"';
		$data_attr  = ' data-lucide="' . $icon_lucide . '"';

		return $tag_open . $pre_attrs . $class_attr . $data_attr . $post_attrs;
	}, $changed );

	$changed = $changed2;

	// Pass 2: PHP-concatenated class string. e.g. class='."'".'mdi mdi-trash-can mr-2'."'".'
	// Replace inner single-quoted icon tokens by translating each glyph via $lucide_map.
	$php_class_pattern = "~class='\\s*\\.\\s*\"\\s*'\\s*\"\\s*\\.\\s*'([^']+)'\\s*\\.\\s*\"\\s*'\\s*\"\\s*\\.\\s*'~";
	$replaced_php = 0;
	$changed3 = preg_replace_callback( $php_class_pattern, function ( $m ) use ( $lucide_map, &$replaced_php ) {
		$inner = $m[1];
		$tokens = preg_split( '/\s+/', trim( $inner ) );
		$kept = array();
		$icon = null;
		$is_whatsapp = false;
		foreach ( $tokens as $idx => $tok ) {
			if ( $idx >= 1 && in_array( $tokens[ $idx - 1 ], array( 'fas', 'far', 'fab', 'mdi', 'dripicons', 'glyphicon', 'feather' ), true ) ) {
				if ( isset( $lucide_map[ $tok ] ) ) {
					$icon = $lucide_map[ $tok ];
					$replaced_php++;
					if ( $icon === '__whatsapp_svg__' ) {
						$is_whatsapp = true;
					}
					continue;
				}
			}
			if ( preg_match( '/^(fas|far|fab|mdi|dripicons|glyphicon|feather)$/', $tok ) ) {
				continue;
			}
			$kept[] = $tok;
		}
		if ( $icon === null ) {
			return $m[0];
		}
		$kept_str = trim( implode( ' ', $kept ) );
		// Wrap in same PHP-concat outer quote scheme. Format: class='."'".'kept'."'".'
		$class_wrap_open  = "class='.\"'.\"'" . $kept_str . "'\".\"'";
		if ( $is_whatsapp ) {
			// Append data-lucide SVG indicator; render <i> wrapper so Lucide could still draw an icon if any.
			// For WhatsApp we use a static <img> via innerHTML — but we can also use data-lucide and let
			// lucide-init handle. Simpler: data-lucide stays and frontend substitutes.
			return $class_wrap_open . ' data-lucide="message-circle"';
		}
		// Standard output: class='."'".'kept-tokens'."'".' data-lucide="icon"
		return $class_wrap_open . ' data-lucide=\'' . $icon . "'";
	}, $changed );

	$changed = $changed3 ? $changed3 : $changed;
	$replaced_in_file += $replaced_php;
	// Skip — covered by regex above.

	// Strip the legacy CDN links.
	$changed = str_replace(
		'<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">',
		'<!-- FontAwesome 4 CDN removed: icons migrated to Lucide -->',
		$changed
	);
	$changed = str_replace(
		'<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/icon?family=Material+Icons">',
		'<!-- Google Material Icons font removed: icons migrated to Lucide -->',
		$changed
	);
	$changed = str_replace(
		'<link href="<?php echo plugin_dir_url( __FILE__ ); ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />',
		'<!-- icons.min.css removed: icons migrated to Lucide (lucide.min.js loaded below) -->',
		$changed
	);

	if ( $changed !== $orig ) {
		file_put_contents( $file, $changed );
		echo "OK ($replaced_in_file icons replaced): $file\n";
		$total_replaced += $replaced_in_file;
	} else {
		echo "NO-OP: $file\n";
	}
}

echo "\nTotal icon classes migrated: $total_replaced\n";

// Helper for WhatsApp image path — placeholder; real path resolves at runtime.
function plugin_dir_url_compat( $_unused ) {
	// Return relative base; caller knows plugin root.
	return '';
}
