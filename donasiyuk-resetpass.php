<?php

	global $wpdb;
	global $wp;
    $table_name = $wpdb->prefix . "dyk_users";
    $table_name2 = $wpdb->prefix . "dyk_settings";
    $table_name3 = $wpdb->prefix . "dyk_password_reset";

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

	$reset_code = $donasi_id;
	if(empty($reset_code)){
		wp_redirect( get_site_url() );
		exit;
	}else{
		$check = $wpdb->get_results('SELECT * from '.$table_name3.' where reset_code="'.$reset_code.'" and  reset_status="0" ')[0] ?? null;
		if($check == null){
			wp_redirect( get_site_url() );
			exit;
		}
	}

	$timefromdatabase = strtotime($check->created_at);
	$diff = time() - $timefromdatabase;
	$is_expired = ($diff > 86400); // 24 hours
?>
<!DOCTYPE html>
<html lang="id-ID">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=0">
	<meta name="application-name" content="<?php echo esc_url($home_url); ?>"/>
	<meta property="og:url" content="<?php echo esc_url($home_url); ?>" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="Atur Kata Sandi Baru - <?php echo esc_attr($app_name); ?>" />
	<meta property="og:description" content="<?php echo esc_attr($app_name); ?>" />
	<?php if(!empty($logo_url)){ ?>
	<meta property="og:image" content="<?php echo esc_url($logo_url); ?>" />
	<?php } ?>
	<title>Atur Kata Sandi Baru &lsaquo; <?php echo esc_html($app_name); ?></title>
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
		.dyk-password-wrap {
			position: relative;
		}
		.dyk-generate-btn {
			position: absolute;
			right: 4px;
			top: 4px;
			height: 34px;
			padding: 0 10px;
			font-size: 12px;
			font-weight: 600;
			color: #2271b1;
			background: #f0f0f1;
			border: 1px solid #c3c4c7;
			border-radius: 3px;
			cursor: pointer;
			display: flex;
			align-items: center;
			justify-content: center;
			transition: all 0.15s ease;
		}
		.dyk-generate-btn:hover {
			background: #e0e0e1;
			color: #135e96;
		}
		.dyk-password-hint {
			font-size: 12px;
			color: #646970;
			margin-top: 6px;
			line-height: 1.4;
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
			
			<?php if($is_expired){ ?>
				<div class="dyk-notice notice-error" role="alert">
					<strong>Tautan Kedaluwarsa</strong>: Tautan reset kata sandi Anda sudah tidak berlaku lagi. Silakan ajukan permohonan tautan baru.
				</div>
			<?php } else { ?>
				
				<div class="dyk-notice" id="setpass-notice" role="alert">
					Masukkan kata sandi baru Anda di bawah ini atau buat kata sandi acak yang kuat.
				</div>

				<form id="dyk-setpass-form" onsubmit="return false;">
					
					<div class="dyk-form-group" id="group-newpass">
						<label class="dyk-form-label" for="new_password">Kata Sandi Baru</label>
						<div class="dyk-password-wrap">
							<input id="new_password" type="text" class="dyk-form-input" name="new_password" style="padding-right: 90px;" autocomplete="new-password" autofocus required />
							<button type="button" class="dyk-generate-btn" id="generatePass">Buat Acak</button>
						</div>
						<div class="dyk-password-hint">Minimal 8 karakter (kombinasi huruf besar, huruf kecil, dan angka).</div>
					</div>

					<div class="dyk-form-group" id="group-confirmpass">
						<label class="dyk-form-label" for="confirm_password">Konfirmasi Kata Sandi Baru</label>
						<input id="confirm_password" type="text" class="dyk-form-input" name="confirm_password" autocomplete="new-password" required />
					</div>

					<div class="dyk-form-group" id="group-btn" style="margin-bottom: 0;">
						<button type="submit" class="dyk-btn-submit" id="reset_password">
							<span class="dyk-spinner" id="setpass-spinner"></span>
							<!-- Lock / Check Icon -->
							<svg class="dyk-btn-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
								<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
								<path d="M7 11V7a5 5 0 0 1 10 0v4"/>
							</svg>
							<span class="dyk-btn-text">Simpan Kata Sandi Baru</span>
						</button>
					</div>

				</form>

			<?php } ?>

		</div>

		<!-- Links Below Card -->
		<div class="dyk-nav-links">
			<div class="dyk-nav-row">
				<?php if($is_expired){ ?>
					<a href="<?php echo esc_url($home_url); ?>/changepass">Minta Tautan Baru</a>
				<?php } ?>

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

		function randomStringPass(len) {
			var charSet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*';
			var pass = '';
			for (var i = 0; i < len; i++) {
				var pos = Math.floor(Math.random() * charSet.length);
				pass += charSet.charAt(pos);
			}
			return pass;
		}

		$('#generatePass').on('click', function() {
			var generated = randomStringPass(12);
			$('#new_password').val(generated);
			$('#confirm_password').val(generated);
		});

		function showNotice(message, type) {
			var $notice = $('#setpass-notice');
			$notice.removeClass('notice-error notice-success');
			if (type === 'error') {
				$notice.addClass('notice-error');
			} else if (type === 'success') {
				$notice.addClass('notice-success');
			}
			$notice.html(message).show();
		}

		function doSetNewPassword() {
			var newPassword = $('#new_password').val().trim();
			var confirmPassword = $('#confirm_password').val().trim();
			var resetEmail = '<?php echo esc_js($check->reset_email ?? ""); ?>';
			var resetCode = '<?php echo esc_js($reset_code ?? ""); ?>';

			if (!newPassword || !confirmPassword) {
				showNotice('<strong>KESALAHAN</strong>: Silakan isi kedua kolom kata sandi.', 'error');
				return false;
			}

			if (newPassword !== confirmPassword) {
				showNotice('<strong>KESALAHAN</strong>: Konfirmasi kata sandi tidak cocok dengan kata sandi baru.', 'error');
				$('#confirm_password').val('').focus();
				return false;
			}

			var $btn = $('#reset_password');
			var $spinner = $('#setpass-spinner');
			var $btnIcon = $btn.find('.dyk-btn-icon');
			var $btnText = $btn.find('.dyk-btn-text');

			$btn.prop('disabled', true);
			$spinner.show();
			$btnIcon.hide();
			$btnText.text('Menyimpan...');

			var data = {
				action: 'dykfunction_reset_pass',
				datanya: [newPassword, resetEmail, resetCode]
			};

			$.post('<?php echo esc_url($home_url); ?>/wp-admin/admin-ajax.php', data, function(response) {
				$btn.prop('disabled', false);
				$spinner.hide();
				$btnIcon.show();
				$btnText.text('Simpan Kata Sandi Baru');

				if (response === 'success') {
					showNotice('<strong>BERHASIL</strong>: Kata sandi Anda telah berhasil diperbarui. <br><a href="<?php echo esc_url($home_url . "/" . $page_login); ?>" style="font-weight:600;text-decoration:underline;">Klik di sini untuk Masuk</a>.', 'success');
					$('#group-newpass, #group-confirmpass, #group-btn').slideUp(200);
				} else if (response === 'password_failed') {
					showNotice('<strong>KESALAHAN</strong>: Kata sandi minimal harus 8 karakter (mengandung huruf besar, huruf kecil, dan angka).', 'error');
				} else {
					showNotice('<strong>KESALAHAN</strong>: Gagal memperbarui kata sandi. Silakan coba kembali.', 'error');
				}
			}).fail(function() {
				$btn.prop('disabled', false);
				$spinner.hide();
				$btnIcon.show();
				$btnText.text('Simpan Kata Sandi Baru');
				showNotice('<strong>KESALAHAN</strong>: Terjadi gangguan jaringan. Silakan coba lagi.', 'error');
			});
		}

		$('#dyk-setpass-form').on('submit', function(e) {
			e.preventDefault();
			doSetNewPassword();
		});

	})(jQuery);
	</script>
</body>
</html>
