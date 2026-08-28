<?php

	global $wpdb;
	global $wp;
    $table_name = $wpdb->prefix . "dyk_users";
    $table_name2 = $wpdb->prefix . "dyk_settings";
    $table_name3 = $wpdb->prefix . "dyk_password_reset_log";
    $table_name4 = $wpdb->prefix . "dyk_password_reset";

    // Check IP User
    check_blocked_ip();

    // Settings
    $query_settings = $wpdb->get_results('SELECT data from '.$table_name2.' where type="logo_url" or type="app_name" or type="login_setting" or type="login_text" or type="register_setting" or type="register_text" or type="page_login" or type="page_register" or type="theme_color" or type="powered_by_setting" or type="changepass_setting" ORDER BY id ASC');
    $logo_url 			= $query_settings[0]->data;
    $app_name 			= $query_settings[1]->data;
    $login_setting 		= $query_settings[2]->data;
    $login_text 		= $query_settings[3]->data;
    $register_setting 	= $query_settings[4]->data;
    $register_text 		= $query_settings[5]->data;
    $page_login 		= $query_settings[6]->data;
    $page_register 		= $query_settings[7]->data;
    $general_theme_color = json_decode($query_settings[8]->data, true);
    $powered_by_setting = $query_settings[9]->data;
    $changepass_setting = $query_settings[10]->data;

	$id_login = wp_get_current_user()->ID;
	
	if($changepass_setting != '1'){
		wp_redirect( get_site_url() );
		exit;
	}

	if(!empty($id_login) && $id_login != '0'){
		wp_redirect( get_site_url() );
		exit;
	}

	$home_url = get_site_url();

	// Custom color fallback
    $theme_color 		= !empty($general_theme_color['color'][0]) ? $general_theme_color['color'][0] : '#2271b1';
	$button_color 		= !empty($general_theme_color['color'][2]) ? $general_theme_color['color'][2] : '#2271b1';

	$user_ip 	  = donasiyuk_getIP();
	$user_os      = donasiyuk_getOS();
	$user_browser = donasiyuk_getBrowser();
	$today		  = date('Y-m-d H:i:s');
	$date = date("Y-m-d H:i:s", strtotime('-1 hours', time()));

	$count_access = $wpdb->get_results('SELECT id from '.$table_name3.' where ip="'.$user_ip.'" and os="'.$user_os.'" and  browser="'.$user_browser.'" and created_at between "'.$date.'" and "'.$today.'" ORDER BY id ASC');
	$check_count_access = count($count_access);
	$count_access2 = $wpdb->get_results('SELECT id from '.$table_name4.' where ip="'.$user_ip.'" and os="'.$user_os.'" and  browser="'.$user_browser.'" and created_at between "'.$date.'" and "'.$today.'" ORDER BY id ASC');
	$check_count_access2 = count($count_access2);

	$is_limited = ($check_count_access >= 5 || $check_count_access2 >= 5);

?>
<!DOCTYPE html>
<html lang="id-ID">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=0">
	<meta name="application-name" content="<?php echo esc_url($home_url); ?>"/>
	<meta property="og:url" content="<?php echo esc_url($home_url); ?>" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="Reset Password - <?php echo esc_attr($app_name); ?>" />
	<meta property="og:description" content="<?php echo esc_attr($app_name); ?>" />
	<?php if(!empty($logo_url)){ ?>
	<meta property="og:image" content="<?php echo esc_url($logo_url); ?>" />
	<?php } ?>
	<title>Lupa Kata Sandi &lsaquo; <?php echo esc_html($app_name); ?></title>
	<?php dyk_set_favicon(); ?>
	<style type="text/css">
		* {
			box-sizing: border-box;
		}
		body {
			background: #f0f0f1;
			color: #3c434a;
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			font-size: 14px;
			line-height: 1.4;
			margin: 0;
			padding: 0;
			min-height: 100vh;
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
		}
		.dyk-login-wrap {
			width: 100%;
			max-width: 400px;
			padding: 30px 20px;
			margin: auto;
		}
		.dyk-login-logo {
			text-align: center;
			margin-bottom: 24px;
		}
		.dyk-login-logo a {
			display: inline-block;
			text-decoration: none;
			outline: none;
		}
		.dyk-login-logo img {
			max-width: 200px;
			max-height: 70px;
			width: auto;
			height: auto;
			object-fit: contain;
			vertical-align: middle;
		}
		.dyk-login-logo h1 {
			font-size: 22px;
			font-weight: 600;
			color: #1d2327;
			margin: 0;
			letter-spacing: -0.3px;
		}
		.dyk-login-card {
			background: #ffffff;
			border: 1px solid #c3c4c7;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
			border-radius: 4px;
			padding: 26px 24px 28px 24px;
			position: relative;
		}
		.dyk-notice {
			background: #ffffff;
			border-left: 4px solid #2271b1;
			box-shadow: 0 1px 2px rgba(0,0,0,0.05);
			padding: 12px 14px;
			margin-bottom: 20px;
			font-size: 13px;
			line-height: 1.5;
			color: #3c434a;
			border-radius: 2px;
			animation: dykFade 0.2s ease-in-out;
		}
		.dyk-notice.notice-error {
			border-left-color: #d63638;
		}
		.dyk-notice.notice-success {
			border-left-color: #00a32a;
		}
		@keyframes dykFade {
			from { opacity: 0; transform: translateY(-4px); }
			to { opacity: 1; transform: translateY(0); }
		}
		.dyk-form-group {
			margin-bottom: 18px;
		}
		.dyk-form-label {
			display: block;
			font-size: 14px;
			font-weight: 500;
			margin-bottom: 6px;
			color: #2c3338;
		}
		.dyk-form-input {
			width: 100%;
			height: 42px;
			padding: 0 12px;
			font-size: 15px;
			color: #2c3338;
			border: 1px solid #8c8f94;
			border-radius: 4px;
			background-color: #ffffff;
			transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
			outline: none;
		}
		.dyk-form-input:focus {
			border-color: <?php echo esc_attr($button_color); ?>;
			box-shadow: 0 0 0 1px <?php echo esc_attr($button_color); ?>;
		}
		.dyk-btn-submit {
			width: 100%;
			height: 44px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			background: <?php echo esc_attr($button_color); ?>;
			border: 1px solid <?php echo esc_attr($button_color); ?>;
			border-radius: 4px;
			color: #ffffff;
			font-size: 14px;
			font-weight: 600;
			cursor: pointer;
			text-decoration: none;
			transition: background-color 0.15s ease, border-color 0.15s ease, filter 0.15s ease;
			margin-top: 8px;
		}
		.dyk-btn-submit:hover {
			filter: brightness(0.9);
			color: #ffffff;
		}
		.dyk-btn-submit:active {
			filter: brightness(0.8);
			transform: translateY(1px);
		}
		.dyk-btn-submit:disabled {
			opacity: 0.65;
			cursor: not-allowed;
		}
		.dyk-btn-submit svg {
			width: 17px;
			height: 17px;
			stroke-width: 2.2;
		}
		.dyk-spinner {
			display: none;
			width: 16px;
			height: 16px;
			border: 2px solid rgba(255, 255, 255, 0.35);
			border-radius: 50%;
			border-top-color: #ffffff;
			animation: dykSpin 0.6s linear infinite;
		}
		@keyframes dykSpin {
			to { transform: rotate(360deg); }
		}
		.dyk-nav-links {
			margin-top: 20px;
			font-size: 13px;
			color: #646970;
		}
		.dyk-nav-links a {
			color: <?php echo esc_attr($button_color); ?>;
			text-decoration: none;
			transition: color 0.15s ease;
		}
		.dyk-nav-links a:hover {
			color: #135e96;
			text-decoration: underline;
		}
		.dyk-nav-row {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 12px;
			padding: 0 2px;
		}
		.dyk-back-home {
			text-align: center;
			margin-top: 14px;
		}
		.dyk-powered-footer {
			margin-top: 26px;
			text-align: center;
			font-size: 12px;
			color: #a7aaad;
		}
		.dyk-powered-footer a {
			color: #a7aaad;
			text-decoration: none;
		}
		.dyk-powered-footer a:hover {
			color: #646970;
			text-decoration: underline;
		}
	</style>
</head>
<body>
	
	<div class="dyk-login-wrap">
		
		<!-- Brand / Logo Area -->
		<div class="dyk-login-logo">
			<a href="<?php echo esc_url($home_url); ?>" title="<?php echo esc_attr($app_name); ?>">
				<?php if(!empty($logo_url)){ ?>
					<img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($app_name); ?>" />
				<?php } else { ?>
					<h1><?php echo esc_html($app_name); ?></h1>
				<?php } ?>
			</a>
		</div>

		<!-- Reset Password Card -->
		<div class="dyk-login-card">
			
			<?php if($is_limited){ ?>
				<div class="dyk-notice notice-error" role="alert">
					<strong>Akses Terbatas</strong>: Anda telah melakukan lebih dari 5 kali percobaan reset kata sandi. Silakan coba kembali dalam 1 jam ke depan.
				</div>
			<?php } else { ?>
				
				<!-- Informative Initial Notice / Error Box -->
				<div class="dyk-notice" id="reset-notice" role="alert">
					Lupa kata sandi Anda? Silakan masukkan alamat email Anda. Anda akan menerima tautan untuk membuat kata sandi baru melalui email.
				</div>

				<form id="dyk-reset-form" onsubmit="return false;">
					
					<div class="dyk-form-group" id="group-email">
						<label class="dyk-form-label" for="email">Nama Pengguna atau Alamat Email</label>
						<input id="email" type="email" class="dyk-form-input" name="email" autocomplete="email" autofocus required />
					</div>

					<div class="dyk-form-group" id="group-btn" style="margin-bottom: 0;">
						<button type="submit" class="dyk-btn-submit" id="reset_password">
							<span class="dyk-spinner" id="reset-spinner"></span>
							<!-- Mail / Key Icon -->
							<svg class="dyk-btn-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
								<rect width="20" height="16" x="2" y="4" rx="2"/>
								<path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
							</svg>
							<span class="dyk-btn-text">Dapatkan Kata Sandi Baru</span>
						</button>
					</div>

				</form>

			<?php } ?>

		</div>

		<!-- Links Below Card -->
		<div class="dyk-nav-links">
			<div class="dyk-nav-row">
				<?php if($login_setting == '1'){ ?>
					<a href="<?php echo esc_url($home_url . '/' . $page_login); ?>">Masuk</a>
				<?php } else { echo '<span></span>'; } ?>

				<?php if($register_setting == '1'){ ?>
					<a href="<?php echo esc_url($home_url . '/' . $page_register); ?>">Daftar Akun Baru</a>
				<?php } ?>
			</div>
			<div class="dyk-back-home">
				<a href="<?php echo esc_url($home_url); ?>">&larr; Kembali ke <?php echo esc_html($app_name); ?></a>
			</div>
		</div>

		<!-- Powered by Footer -->
		<?php if($powered_by_setting == '1'){ ?>
		<div class="dyk-powered-footer">
			<a href="https://donasiyuk.id" target="_blank">Powered by DonasiYuk</a>
		</div>
		<?php } ?>

	</div>

	<script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/jquery.min.js';?>"></script>
	<script>
	(function($) {
		'use strict';

		function validateEmail(email) {
			var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			return re.test(email);
		}

		function showNotice(message, type) {
			var $notice = $('#reset-notice');
			$notice.removeClass('notice-error notice-success');
			if (type === 'error') {
				$notice.addClass('notice-error');
			} else if (type === 'success') {
				$notice.addClass('notice-success');
			}
			$notice.html(message).show();
		}

		function doResetPassword() {
			var email = $('#email').val().trim();

			if (!email) {
				showNotice('<strong>KESALAHAN</strong>: Silakan isi alamat email Anda.', 'error');
				return false;
			}
			if (!validateEmail(email)) {
				showNotice('<strong>KESALAHAN</strong>: Alamat email yang Anda masukkan tidak valid.', 'error');
				return false;
			}

			var $btn = $('#reset_password');
			var $spinner = $('#reset-spinner');
			var $btnIcon = $btn.find('.dyk-btn-icon');
			var $btnText = $btn.find('.dyk-btn-text');

			$btn.prop('disabled', true);
			$spinner.show();
			$btnIcon.hide();
			$btnText.text('Mengirim tautan...');

			var data = {
				action: 'dykfunction_send_link',
				datanya: [email]
			};

			$.post('<?php echo esc_url($home_url); ?>/wp-admin/admin-ajax.php', data, function(response) {
				$btn.prop('disabled', false);
				$spinner.hide();
				$btnIcon.show();
				$btnText.text('Dapatkan Kata Sandi Baru');

				if (response === 'success') {
					showNotice('<strong>BERHASIL</strong>: Tautan reset kata sandi telah dikirimkan ke email <strong>' + $('<div>').text(email).html() + '</strong>. Silakan periksa kotak masuk atau folder spam Anda.', 'success');
					$('#group-email, #group-btn').slideUp(200);
				} else if (response === 'not_valid') {
					showNotice('<strong>KESALAHAN</strong>: Tidak ada akun yang terdaftar dengan alamat email tersebut.', 'error');
				} else if (response === 'limitted_access' || response === 'limitted_access2') {
					showNotice('<strong>KESALAHAN</strong>: Anda telah melakukan lebih dari 5 kali percobaan. Silakan coba kembali dalam 1 jam.', 'error');
					setTimeout(function() {
						window.location.reload();
					}, 2000);
				} else {
					showNotice('<strong>KESALAHAN</strong>: Gagal mengirim email reset kata sandi. Silakan hubungi pengelola situs.', 'error');
				}
			}).fail(function() {
				$btn.prop('disabled', false);
				$spinner.hide();
				$btnIcon.show();
				$btnText.text('Dapatkan Kata Sandi Baru');
				showNotice('<strong>KESALAHAN</strong>: Terjadi gangguan jaringan. Silakan coba lagi.', 'error');
			});
		}

		$('#dyk-reset-form').on('submit', function(e) {
			e.preventDefault();
			doResetPassword();
		});

	})(jQuery);
	</script>
</body>
</html>
