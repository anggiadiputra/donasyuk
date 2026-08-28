<?php

	global $wpdb;
	global $wp;
    $table_name = $wpdb->prefix . "dyk_users";
    $table_name2 = $wpdb->prefix . "dyk_settings";
    $table_name3 = $wpdb->prefix . "dyk_donate";
    $table_name4 = $wpdb->prefix . "dyk_campaign";
    $table_name5 = $wpdb->prefix . "options";
    $table_name6 = $wpdb->prefix . "dyk_users";

    // $id_login = wp_get_current_user()->ID;
    // if($id_login==null){
    // 	echo 'You are not allowed.';
    // 	die;
    // }

	if (isset($_GET['inv']) && !empty($_GET['inv'])) {
		$invoice_id = sanitize_text_field($_GET['inv']);
	} elseif (!empty($donasi_id)) {
		$invoice_id = sanitize_text_field($donasi_id);
	} else {
		$invoice_id = '';
	}

	// Data Donasi (Dapat diakses publik melalui Invoice ID unik untuk validasi bukti transaksi resmi)
	$donation_query = $wpdb->get_results($wpdb->prepare(
		'SELECT a.*, b.title, b.slug FROM ' . $table_name3 . ' a 
		LEFT JOIN ' . $table_name4 . ' b ON b.campaign_id = a.campaign_id 
		WHERE a.invoice_id = %s',
		$invoice_id
	));
	
	if (empty($donation_query)) {
		wp_redirect( get_site_url() );
		exit;
	}
	$donation = $donation_query[0];

	// Data Settings
    $query_settings = $wpdb->get_results('SELECT data from '.$table_name2.' where type="logo_url" or type="app_name" or type="page_typ" or type="theme_color" or type="currency" ORDER BY id ASC');
    $logo_url 		= $query_settings[0]->data;
    $app_name 		= $query_settings[1]->data;
    $page_typ 			 = $query_settings[2]->data;
    $general_theme_color = json_decode($query_settings[3]->data, true);
    $currency			 = $query_settings[4]->data;

    $theme_color 		= $general_theme_color['color'][0];
	$progressbar_color  = $general_theme_color['color'][1];
	$button_color 		= $general_theme_color['color'][2];

	if($button_color==''){
		$button_color = '#dc2f6a';
	}
	if($theme_color==''){
        $theme_color = '#000';
    }

	if($currency=='IDR'){
    	$value_currency = ' Rupiah';
    }elseif($currency=='MYR'){
    	$value_currency = ' Ringgit';
    }else{
    	$value_currency = ' Rupiah';
    }

	// GET URL WEB
	$row = $wpdb->get_results('SELECT option_value from '.$table_name5.' where option_name="siteurl"');
	$row = $row[0];

    $protocols = array('http://', 'http://www.', 'www.', 'https://', 'https://');
	$server = str_replace($protocols, '', $row->option_value);

	// URL resmi E-Kuitansi untuk validasi QR Code
	$qr_link = get_site_url().'/ekuitansi/'.$invoice_id;

	// Data USer admin
	$profile = $wpdb->get_results('SELECT *, user_pp_img as photo from '.$table_name6.' where user_id="1"');

	// Currency
    $query_currency = $wpdb->get_results('SELECT data from '.$table_name2.' where type="currency"  ORDER BY id ASC');
    $currency = $query_currency[0]->data;
    $lang = get_data_lang($currency);
    $langArray = require_once(ROOTDIR_DYK . 'library/locale/'.$lang.'.php');

    $show_currency = donasiyuk_currency($currency);
    $show_currency2 = donasiyuk_currency2($currency);

    $alamat = '-';
    if($donation->info_donate!=null && $donation->info_donate!='[]') { 

		$info_donate = json_decode($donation->info_donate, true);
		$count_qurban = 0;
	    foreach ( $info_donate as $key => $value ) {
	    	if(strpos(strtolower($key), 'alamat') !== false){
	    		$alamat = $value;
	    	}
	    }

	}

	if($alamat == ''){$alamat = '-';}

	// Tanggal Donasi
	$date_raw = !empty($donation->created_at) ? $donation->created_at : (!empty($donation->date) ? $donation->date : 'now');
	try {
		$datenya = new DateTime($date_raw);
	} catch (Exception $e) {
		$datenya = new DateTime('now');
	}

	$month_en = $datenya->format('F');
	$month_names = array(
		'January'   => 'Januari',
		'February'  => 'Februari',
		'March'     => 'Maret',
		'April'     => 'April',
		'May'       => 'Mei',
		'June'      => 'Juni',
		'July'      => 'Juli',
		'August'    => 'Agustus',
		'September' => 'September',
		'October'   => 'Oktober',
		'November'  => 'November',
		'December'  => 'Desember'
	);
	$month_id = isset($month_names[$month_en]) ? $month_names[$month_en] : $datenya->format('M');
	$date_donation = $datenya->format('d') . ' ' . $month_id . ' ' . $datenya->format('Y') . ' - ' . $datenya->format('H:i');

?>

<!DOCTYPE html>
<html lang="id">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo $app_name; ?> - <?php echo get_langArray('kuitansi_title');?> #<?php echo $invoice_id; ?></title>
		
		<!-- Google Fonts -->
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
		
		<!-- html2canvas for Download as Image -->
		<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

		<style>
			@page { 
				size: A4; 
				margin: 10mm auto; 
			}
			
			* {
				margin: 0;
				padding: 0;
				box-sizing: border-box;
				-webkit-print-color-adjust: exact !important;
				print-color-adjust: exact !important;
			}

			:root {
				--primary-color: #0f172a;
				--primary-light: #f1f5f9;
				--text-main: #0f172a;
				--text-muted: #64748b;
				--text-light: #94a3b8;
				--border-color: #e2e8f0;
				--bg-card: #f8fafc;
				--bg-page: #f1f5f9;
			}

			body {
				background-color: var(--bg-page);
				font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
				color: var(--text-main);
				line-height: 1.5;
				-webkit-font-smoothing: antialiased;
				padding: 24px 12px;
			}

			.receipt-wrapper {
				max-width: 440px;
				margin: 0 auto;
			}

			/* Action Bar */
			.print-action-bar {
				display: flex;
				justify-content: center;
				gap: 8px;
				margin-bottom: 14px;
				flex-wrap: wrap;
			}

			.btn-action {
				display: inline-flex;
				align-items: center;
				gap: 6px;
				background: #ffffff;
				color: var(--text-main);
				border: 1px solid var(--border-color);
				padding: 7px 13px;
				font-size: 12px;
				font-weight: 600;
				border-radius: 4px;
				cursor: pointer;
				text-decoration: none;
				box-shadow: 0 1px 2px rgba(0,0,0,0.04);
				transition: all 0.15s ease;
			}

			.btn-action:hover {
				background: #f8fafc;
				border-color: #cbd5e1;
			}

			.btn-action.btn-primary-action {
				background: #0f172a;
				color: #ffffff;
				border-color: #0f172a;
			}

			.btn-action.btn-primary-action:hover {
				background: #1e293b;
			}

			/* Mobile Vertical Receipt Card */
			.receipt-card {
				background: #ffffff;
				border-radius: 6px;
				border: 1px solid var(--border-color);
				box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05);
				overflow: hidden;
				position: relative;
			}

			.receipt-body {
				padding: 22px 20px;
			}

			/* Brand Header */
			.receipt-header {
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 12px;
				padding-bottom: 14px;
			}

			.brand-left {
				display: flex;
				align-items: center;
				gap: 10px;
			}

			.brand-logo {
				width: 42px;
				height: 42px;
				object-fit: contain;
				border-radius: 4px;
				background: #ffffff;
				padding: 2px;
				border: 1px solid var(--border-color);
			}

			.brand-text h2 {
				font-size: 16px;
				font-weight: 800;
				color: var(--text-main);
				letter-spacing: -0.02em;
				line-height: 1.2;
			}

			.brand-text span {
				font-size: 12px;
				color: var(--text-muted);
				font-weight: 500;
			}

			.doc-type-badge {
				font-size: 10px;
				font-weight: 700;
				text-transform: uppercase;
				letter-spacing: 0.06em;
				color: #475569;
				background: #f1f5f9;
				border: 1px solid #e2e8f0;
				padding: 3px 8px;
				border-radius: 3px;
			}

			/* Hero Amount Section */
			.receipt-hero {
				text-align: center;
				padding: 14px 12px;
				background: var(--bg-card);
				border: 1px solid var(--border-color);
				border-radius: 4px;
				margin-top: 2px;
			}

			.status-pill {
				display: inline-flex;
				align-items: center;
				gap: 5px;
				font-size: 10.5px;
				font-weight: 700;
				padding: 2px 8px;
				border-radius: 3px;
				text-transform: uppercase;
				letter-spacing: 0.04em;
				margin-bottom: 6px;
			}

			.status-pill.paid {
				background-color: #dcfce7;
				color: #15803d;
				border: 1px solid #bbf7d0;
			}

			.status-pill.unpaid {
				background-color: #fef3c7;
				color: #b45309;
				border: 1px solid #fde68a;
			}

			.status-dot {
				width: 5px;
				height: 5px;
				border-radius: 50%;
			}

			.status-pill.paid .status-dot {
				background-color: #16a34a;
			}

			.status-pill.unpaid .status-dot {
				background-color: #d97706;
			}

			.hero-nominal-label {
				font-size: 10px;
				font-weight: 700;
				text-transform: uppercase;
				letter-spacing: 0.06em;
				color: var(--text-muted);
				margin-bottom: 2px;
			}

			.hero-nominal-val {
				font-size: 24px;
				font-weight: 800;
				color: var(--text-main);
				letter-spacing: -0.03em;
				line-height: 1.15;
				margin-bottom: 6px;
			}

			.terbilang-quote {
				background: #ffffff;
				border: 1px dashed #cbd5e1;
				border-radius: 4px;
				padding: 5px 8px;
				font-size: 11px;
				color: #475569;
				line-height: 1.35;
				font-style: italic;
			}

			/* Perforated Divider */
			.receipt-divider {
				display: flex;
				align-items: center;
				margin: 16px 0;
			}

			.receipt-divider-line {
				flex-grow: 1;
				border-bottom: 1.5px dashed #cbd5e1;
			}

			/* Key-Value Details Rows */
			.receipt-details-list {
				display: flex;
				flex-direction: column;
				gap: 10px;
			}

			.detail-row {
				display: flex;
				justify-content: space-between;
				align-items: flex-start;
				gap: 12px;
				font-size: 12.5px;
			}

			.detail-label {
				color: var(--text-muted);
				font-weight: 500;
				flex-shrink: 0;
				max-width: 40%;
				font-size: 11.5px;
			}

			.detail-val {
				color: var(--text-main);
				font-weight: 700;
				text-align: right;
				word-break: break-word;
				font-size: 12.5px;
			}

			.detail-val.code {
				font-family: 'Space Grotesk', monospace;
				font-size: 12px;
				letter-spacing: -0.01em;
				color: #0f172a;
				background: #f1f5f9;
				border: 1px solid #e2e8f0;
				padding: 2px 6px;
				border-radius: 3px;
			}

			.detail-val-sub {
				display: block;
				font-size: 11px;
				color: var(--text-muted);
				font-weight: 500;
				margin-top: 2px;
			}

			/* QR Verification Center */
			.receipt-qr-card {
				background: var(--bg-card);
				border: 1px solid var(--border-color);
				border-radius: 4px;
				padding: 14px 12px;
				text-align: center;
				margin-top: 16px;
			}

			.qr-box {
				display: inline-block;
				background: #ffffff;
				padding: 5px;
				border-radius: 4px;
				border: 1px solid var(--border-color);
				text-decoration: none;
				margin-bottom: 6px;
			}

			.qr-title {
				font-size: 11.5px;
				font-weight: 700;
				color: var(--text-main);
				display: flex;
				align-items: center;
				justify-content: center;
				gap: 5px;
				margin-bottom: 2px;
			}

			.qr-desc {
				font-size: 10.5px;
				color: var(--text-muted);
				line-height: 1.35;
			}

			/* Footer */
			.receipt-footer {
				margin-top: 16px;
				padding-top: 12px;
				border-top: 1px solid var(--border-color);
				text-align: center;
			}

			.footer-org {
				font-size: 10.5px;
				color: var(--text-muted);
				font-weight: 500;
				line-height: 1.4;
			}

			.footer-org strong {
				color: var(--text-main);
			}

			.footer-disclaimer {
				font-size: 9.5px;
				color: var(--text-light);
				margin-top: 3px;
			}

			/* QR Code Matrix Styles */
			div.qr {
				margin: 0 auto;
				display: inline-block;
				line-height: 0;
			}
			div.qr > div {
				height: 2.5px;
			}
			div.qr > div > span {
				display: inline-block;
				width: 2.5px;
				height: 2.5px;
			}
			span.dark { background: #0f172a; }
			span.light { background: #ffffff; }
			span.data { background: #ffffff; }
			span.data-dark { background: #0f172a; }
			span.finder { background: #ffffff; }
			span.finder-dark { background: #0f172a; }
			span.finder-dot { background: #0f172a; }
			span.alignment { background: #ffffff; }
			span.alignment-dark { background: #0f172a; }
			span.timing { background: #ffffff; }
			span.timing-dark { background: #0f172a; }
			span.format { background: #ffffff; }
			span.format-dark { background: #0f172a; }
			span.version { background: #ffffff; }
			span.version-dark { background: #0f172a; }
			span.darkmodule { background: #0f172a; }
			span.separator { background: #ffffff; }
			span.quietzone { background: transparent; }

			/* Print Styles */
			@media print {
				body {
					background: #ffffff !important;
					padding: 0 !important;
				}
				.print-action-bar {
					display: none !important;
				}
				.receipt-wrapper {
					max-width: 440px !important;
					width: 100% !important;
					margin: 0 auto !important;
				}
				.receipt-card {
					border: 1px solid #cbd5e1 !important;
					box-shadow: none !important;
					border-radius: 4px !important;
				}
				.receipt-body {
					padding: 16px 14px !important;
				}
			}
		</style>
	</head>

	<body>

		<div class="receipt-wrapper">

			<!-- Action Buttons (Hidden on Print) -->
			<div class="print-action-bar">
				<button type="button" class="btn-action" id="btn-download-img" onclick="downloadReceiptImage()">
					<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
						<polyline points="7 10 12 15 17 10"></polyline>
						<line x1="12" y1="15" x2="12" y2="3"></line>
					</svg>
					Simpan Gambar
				</button>
				<button type="button" class="btn-action btn-primary-action" onclick="window.print()">
					<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<polyline points="6 9 6 2 18 2 18 9"></polyline>
						<path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
						<rect x="6" y="14" width="12" height="8"></rect>
					</svg>
					Cetak / PDF
				</button>
			</div>

			<!-- Mobile Vertical Digital Receipt Card -->
			<div class="receipt-card" id="invoice-receipt-card">

				<div class="receipt-body">

					<!-- Brand Header -->
					<div class="receipt-header">
						<div class="brand-left">
							<?php if(!empty($logo_url)){ ?>
								<img alt="<?php echo $app_name; ?>" class="brand-logo" src="<?php echo $logo_url; ?>" onerror="this.style.display='none';">
							<?php } ?>
							<div class="brand-text">
								<h2><?php echo $app_name; ?></h2>
								<span><?php echo $server; ?></span>
							</div>
						</div>
						<div class="doc-type-badge">E-<?php echo get_langArray('kuitansi_title');?></div>
					</div>

					<!-- Hero Amount & Status -->
					<div class="receipt-hero">
						<?php if($donation->status == '1'){ ?>
							<div class="status-pill paid">
								<span class="status-dot"></span>
								<?php echo get_langArray('kuitansi_label3');?>
							</div>
						<?php } else { ?>
							<div class="status-pill unpaid">
								<span class="status-dot"></span>
								BELUM <?php echo get_langArray('kuitansi_label3');?>
							</div>
						<?php } ?>

						<div class="hero-nominal-label"><?php echo get_langArray('kuitansi_label2');?></div>
						<div class="hero-nominal-val">
							<?php echo $show_currency2 . number_format_currency($donation->nominal); ?>
						</div>

						<div class="terbilang-quote">
							“<?php echo ucwords(angka_terbilang($donation->nominal)) . $value_currency; ?>”
						</div>
					</div>

					<!-- Perforated Line -->
					<div class="receipt-divider">
						<div class="receipt-divider-line"></div>
					</div>

					<!-- Transaction Details List -->
					<div class="receipt-details-list">
						<div class="detail-row">
							<span class="detail-label">No. Invoice</span>
							<span class="detail-val code">#<?php echo $invoice_id; ?></span>
						</div>

						<div class="detail-row">
							<span class="detail-label">Waktu Transaksi</span>
							<span class="detail-val"><?php echo $date_donation; ?></span>
						</div>

						<div class="detail-row">
							<span class="detail-label"><?php echo get_langArray('kuitansi_label1');?></span>
							<div class="detail-val">
								<?php echo $donation->name; ?>
								<?php if(!empty($donation->whatsapp)){ ?>
									<span class="detail-val-sub">📞 <?php echo $donation->whatsapp; ?></span>
								<?php } ?>
							</div>
						</div>

						<div class="detail-row">
							<span class="detail-label">Untuk Program</span>
							<span class="detail-val"><?php echo $donation->title; ?></span>
						</div>

						<?php if (strpos(strtolower($donation->title), 'zakat') !== false ) { ?>
							<div class="detail-row">
								<span class="detail-label"><?php echo get_langArray('kuitansi_label4');?></span>
								<span class="detail-val"><?php echo $alamat; ?></span>
							</div>
						<?php } ?>
					</div>

					<!-- QR Code & Security Verification -->
					<div class="receipt-qr-card">
						<a href="<?php echo $qr_link; ?>" target="_blank" class="qr-box" title="Klik untuk verifikasi online">
							<div id="qrcode-container-html"></div>
						</a>
						<div class="qr-title">
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0f172a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
								<polyline points="9 12 11 14 15 10"></polyline>
							</svg>
							Validasi Resmi E-Kuitansi
						</div>
						<p class="qr-desc">Pindai kode QR di atas untuk memvalidasi keaslian dokumen di sistem online.</p>
					</div>

					<!-- Footer -->
					<div class="receipt-footer">
						<p class="footer-org">
							<strong><?php echo $app_name; ?></strong><br>
							<?php echo $profile[0]->user_alamat; ?><?php if(!empty($profile[0]->user_kecamatan)) echo ' - ' . $profile[0]->user_kecamatan; ?>, <?php echo $profile[0]->user_kabkota; ?>, <?php echo $profile[0]->user_provinsi; ?>
						</p>
						<p class="footer-disclaimer">Dokumen ini merupakan bukti donasi yang sah dan diterbitkan secara elektronik.</p>
					</div>

				</div>
			</div>
		</div>

		<!-- Scripts -->
		<script src="<?php echo plugin_dir_url( __FILE__ ); ?>plugins/zoom/medium-zoom.min.js"></script>
		<script type="module">
			import {
				QRCode, QROptions, OUTPUT_MARKUP_HTML, IS_DARK,
				M_DATA, M_FINDER, M_FINDER_DOT, M_ALIGNMENT, M_TIMING, M_FORMAT,
				M_VERSION, M_DARKMODULE, M_SEPARATOR, M_QUIETZONE
			} from '<?php echo plugin_dir_url( __FILE__ ); ?>plugins/qr/index.js';

			let mv = {};

			// data
			mv[M_DATA]               = 'data';
			mv[M_DATA|IS_DARK]       = 'data-dark';
			// finder
			mv[M_FINDER]             = 'finder';
			mv[M_FINDER|IS_DARK]     = 'finder-dark';
			mv[M_FINDER_DOT|IS_DARK] = 'finder-dot';
			// alignment
			mv[M_ALIGNMENT]          = 'alignment';
			mv[M_ALIGNMENT|IS_DARK]  = 'alignment-dark';
			// timing
			mv[M_TIMING]             = 'timing';
			mv[M_TIMING|IS_DARK]     = 'timing-dark';
			// format
			mv[M_FORMAT]             = 'format';
			mv[M_FORMAT|IS_DARK]     = 'format-dark';
			// version
			mv[M_VERSION]            = 'version';
			mv[M_VERSION|IS_DARK]    = 'version-dark';
			// darkmodule
			mv[M_DARKMODULE|IS_DARK] = 'darkmodule';
			// separator
			mv[M_SEPARATOR]          = 'separator';
			// quietzone
			mv[M_QUIETZONE]          = 'quietzone';

			let options = new QROptions({
				outputType               : OUTPUT_MARKUP_HTML,
				version                  : 7,
				cssClass                 : 'qr',
				markupDark               : 'dark',
				markupLight              : 'light',
				moduleValues             : mv,
				eol                      : '',
				returnMarkupAsHtmlElement: true,
			});

			let qrcode = (new QRCode(options)).render('<?php echo $qr_link; ?>');
			let container = document.getElementById('qrcode-container-html');
			if (container) {
				container.appendChild(qrcode);
			}
		</script>

		<!-- Save as Image (PNG) Script -->
		<script>
			function downloadReceiptImage() {
				const card = document.getElementById('invoice-receipt-card');
				const btn = document.getElementById('btn-download-img');
				if (!card) return;
				
				const originalText = btn ? btn.innerHTML : '';
				if (btn) {
					btn.innerHTML = '⏳ Menyimpan...';
					btn.disabled = true;
				}

				html2canvas(card, {
					scale: 3,
					useCORS: true,
					backgroundColor: '#ffffff'
				}).then(canvas => {
					const link = document.createElement('a');
					link.download = 'E-Kuitansi-<?php echo $invoice_id; ?>.png';
					link.href = canvas.toDataURL('image/png');
					link.click();
					if (btn) {
						btn.innerHTML = originalText;
						btn.disabled = false;
					}
				}).catch(err => {
					console.error('Error generating receipt image:', err);
					if (btn) {
						btn.innerHTML = originalText;
						btn.disabled = false;
					}
				});
			}
		</script>

	</body>
</html>