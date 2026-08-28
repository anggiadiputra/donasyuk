<?php

	global $wpdb;
	global $wp;
    $table_name = $wpdb->prefix . "dyk_users";
    $table_name2 = $wpdb->prefix . "dyk_settings";

    // Check IP User (rate-limit + IP/CIDR hard block)
    check_blocked_ip();

    // Settings
    $query_settings = $wpdb->get_results('SELECT data from '.$table_name2.' where type="logo_url" or type="app_name" or type="login_setting" or type="login_text" or type="register_setting" or type="register_text" or type="page_login" or type="page_register" or type="theme_color" or type="powered_by_setting" or type="changepass_setting" ORDER BY id ASC');
    $logo_url 			= $query_settings[0]->data ?? '';
    $app_name 			= $query_settings[1]->data ?? 'DonasiYuk';
    $login_setting 		= $query_settings[2]->data ?? '1';
    $login_text 		= $query_settings[3]->data ?? 'Login';
    $register_setting 	= $query_settings[4]->data ?? '1';
    $register_text 		= $query_settings[5]->data ?? 'Daftar';
    $page_login 		= $query_settings[6]->data ?? 'login';
    $page_register 		= $query_settings[7]->data ?? 'register';
    $general_theme_color = (!empty($query_settings[8]->data)) ? json_decode($query_settings[8]->data, true) : [];
    $powered_by_setting = $query_settings[9]->data ?? '0';
    $changepass_setting = $query_settings[10]->data ?? '1';
    
	$id_login = wp_get_current_user()->ID;

	if (!empty($id_login)) {
		wp_redirect( get_site_url().'/wp-admin/admin.php?page=donasiyuk_myprofile' );
		exit;
	}
	if ($login_setting !== '1') {
		wp_redirect( get_site_url() );
		exit;
	}

	$home_url = get_site_url();

	// Custom color fallback if set
    $theme_color 		= !empty($general_theme_color['color'][0]) ? $general_theme_color['color'][0] : '#f95700';
	$button_color 		= !empty($general_theme_color['color'][2]) ? $general_theme_color['color'][2] : (!empty($general_theme_color['color'][0]) ? $general_theme_color['color'][0] : '#f95700');

?>
<!DOCTYPE html>
<html lang="id-ID">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=0">
	<meta name="application-name" content="<?php echo esc_url($home_url); ?>"/>
	<meta property="og:url" content="<?php echo esc_url($home_url); ?>" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="Login - <?php echo esc_attr($app_name); ?>" />
	<meta property="og:description" content="<?php echo esc_attr($app_name); ?>" />
	<?php if(!empty($logo_url)){ ?>
	<meta property="og:image" content="<?php echo esc_url($logo_url); ?>" />
	<?php } ?>
	<title>Login &lsaquo; <?php echo esc_html($app_name); ?></title>
	<?php dyk_set_favicon(); ?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<style type="text/css">
		* {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}
		body {
			background: #ffffff;
			color: #1e293b;
			font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
			font-size: 14px;
			line-height: 1.5;
			min-height: 100vh;
			display: flex;
			overflow-x: hidden;
		}
		.dyk-split-container {
			display: flex;
			width: 100vw;
			min-height: 100vh;
		}

		/* --- LEFT SIDE: Brand Canvas --- */
		.dyk-brand-side {
			flex: 1;
			background: <?php echo esc_attr($button_color); ?>;
			background: linear-gradient(135deg, <?php echo esc_attr($button_color); ?> 0%, <?php echo esc_attr($theme_color); ?> 100%);
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			align-items: center;
			padding: 48px 36px;
			position: relative;
			overflow: hidden;
			color: #ffffff;
		}
		.dyk-brand-side::before {
			content: '';
			position: absolute;
			top: -15%;
			left: -15%;
			width: 60%;
			height: 60%;
			background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
			border-radius: 50%;
			pointer-events: none;
		}
		.dyk-brand-side::after {
			content: '';
			position: absolute;
			bottom: -20%;
			right: -20%;
			width: 70%;
			height: 70%;
			background: radial-gradient(circle, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0) 70%);
			border-radius: 50%;
			pointer-events: none;
		}
		.dyk-brand-top {
			width: 100%;
		}
		.dyk-brand-center {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			text-align: center;
			margin: auto 0;
			z-index: 2;
		}
		.dyk-brand-logo-img {
			max-width: 240px;
			max-height: 90px;
			width: auto;
			height: auto;
			object-fit: contain;
			filter: drop-shadow(0 4px 12px rgba(0,0,0,0.1));
			margin-bottom: 16px;
		}
		.dyk-brand-title {
			font-size: 32px;
			font-weight: 800;
			letter-spacing: -0.5px;
			color: #ffffff;
			text-shadow: 0 2px 10px rgba(0,0,0,0.12);
			display: flex;
			align-items: center;
			gap: 12px;
		}
		.dyk-brand-title svg {
			width: 38px;
			height: 38px;
		}
		.dyk-brand-footer {
			font-size: 13px;
			color: rgba(255, 255, 255, 0.75);
			letter-spacing: 0.2px;
			z-index: 2;
			text-align: center;
		}

		/* --- RIGHT SIDE: Form Canvas --- */
		.dyk-form-side {
			flex: 1;
			background: #ffffff;
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			padding: 40px 24px;
			position: relative;
		}
		.dyk-form-wrapper {
			width: 100%;
			max-width: 380px;
			margin: auto;
		}
		.dyk-form-header {
			margin-bottom: 28px;
		}
		.dyk-form-header h2 {
			font-size: 28px;
			font-weight: 700;
			color: #0f172a;
			letter-spacing: -0.5px;
			margin-bottom: 6px;
		}
		.dyk-form-header p {
			font-size: 14px;
			color: #64748b;
		}

		/* Notice / Alert Box */
		.dyk-notice {
			display: none;
			background: #fef2f2;
			border: 1px solid #fee2e2;
			border-left: 4px solid #ef4444;
			padding: 12px 14px;
			margin-bottom: 20px;
			font-size: 13px;
			line-height: 1.5;
			color: #991b1b;
			border-radius: 6px;
			animation: dykFade 0.2s ease-in-out;
		}
		.dyk-notice.notice-success {
			background: #f0fdf4;
			border-color: #dcfce7;
			border-left-color: #22c55e;
			color: #166534;
		}
		@keyframes dykFade {
			from { opacity: 0; transform: translateY(-4px); }
			to { opacity: 1; transform: translateY(0); }
		}

		/* Soft Input Fields */
		.dyk-input-group {
			margin-bottom: 16px;
		}
		.dyk-input-box {
			position: relative;
			display: flex;
			align-items: center;
			background: #f8fafc;
			border: 1.5px solid #f1f5f9;
			border-radius: 8px;
			transition: all 0.2s ease;
		}
		.dyk-input-box:focus-within {
			background: #ffffff;
			border-color: <?php echo esc_attr($button_color); ?>;
			box-shadow: 0 0 0 3px <?php echo esc_attr($button_color); ?>18;
		}
		.dyk-input-icon {
			position: absolute;
			left: 14px;
			display: flex;
			align-items: center;
			justify-content: center;
			color: <?php echo esc_attr($button_color); ?>;
			pointer-events: none;
		}
		.dyk-input-icon svg {
			width: 18px;
			height: 18px;
		}
		.dyk-form-input {
			width: 100%;
			height: 48px;
			padding: 0 16px 0 44px;
			font-size: 14px;
			font-family: inherit;
			color: #1e293b;
			background: transparent;
			border: none;
			outline: none;
		}
		.dyk-form-input::placeholder {
			color: #94a3b8;
			font-weight: 400;
		}
		.dyk-password-box .dyk-form-input {
			padding-right: 44px;
		}
		.dyk-password-toggle {
			position: absolute;
			right: 0;
			top: 0;
			height: 100%;
			width: 44px;
			display: flex;
			align-items: center;
			justify-content: center;
			background: transparent;
			border: none;
			color: #94a3b8;
			cursor: pointer;
			padding: 0;
			transition: color 0.15s ease;
		}
		.dyk-password-toggle:hover {
			color: #475569;
		}
		.dyk-password-toggle svg {
			width: 18px;
			height: 18px;
		}

		/* Forgot Password Link */
		.dyk-forgot-row {
			display: flex;
			justify-content: flex-start;
			align-items: center;
			margin-top: 6px;
			margin-bottom: 22px;
		}
		.dyk-forgot-link {
			color: <?php echo esc_attr($button_color); ?>;
			font-size: 13px;
			font-weight: 600;
			text-decoration: none;
			transition: opacity 0.2s ease;
		}
		.dyk-forgot-link:hover {
			opacity: 0.85;
			text-decoration: underline;
		}

		/* Submit Button */
		.dyk-btn-submit {
			width: 100%;
			height: 48px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
			background: <?php echo esc_attr($button_color); ?>;
			border: none;
			border-radius: 8px;
			color: #ffffff;
			font-family: inherit;
			font-size: 15px;
			font-weight: 600;
			cursor: pointer;
			text-decoration: none;
			box-shadow: 0 4px 14px <?php echo esc_attr($button_color); ?>40;
			transition: all 0.2s ease;
		}
		.dyk-btn-submit:hover {
			filter: brightness(0.92);
			transform: translateY(-1px);
			box-shadow: 0 6px 18px <?php echo esc_attr($button_color); ?>55;
		}
		.dyk-btn-submit:active {
			filter: brightness(0.85);
			transform: translateY(0);
		}
		.dyk-btn-submit:disabled {
			opacity: 0.65;
			cursor: not-allowed;
			transform: none;
		}
		.dyk-spinner {
			display: none;
			width: 18px;
			height: 18px;
			border: 2px solid rgba(255, 255, 255, 0.35);
			border-radius: 50%;
			border-top-color: #ffffff;
			animation: dykSpin 0.6s linear infinite;
		}
		@keyframes dykSpin {
			to { transform: rotate(360deg); }
		}

		/* Bottom Nav Links */
		.dyk-signup-row {
			text-align: center;
			margin-top: 24px;
			font-size: 13.5px;
			color: #64748b;
		}
		.dyk-signup-row a {
			color: <?php echo esc_attr($button_color); ?>;
			font-weight: 700;
			text-decoration: none;
			margin-left: 4px;
		}
		.dyk-signup-row a:hover {
			text-decoration: underline;
		}
		.dyk-back-home {
			text-align: center;
			margin-top: 18px;
			font-size: 13px;
		}
		.dyk-back-home a {
			color: #94a3b8;
			text-decoration: none;
			transition: color 0.15s ease;
		}
		.dyk-back-home a:hover {
			color: #475569;
		}
		.dyk-powered-footer {
			margin-top: 28px;
			text-align: center;
			font-size: 11.5px;
			color: #cbd5e1;
		}
		.dyk-powered-footer a {
			color: #94a3b8;
			text-decoration: none;
		}
		.dyk-powered-footer a:hover {
			color: #64748b;
			text-decoration: underline;
		}
		.hidden {
			display: none !important;
		}

		/* Mobile Header for small screens */
		.dyk-mobile-brand {
			display: none;
			text-align: center;
			margin-bottom: 24px;
		}
		.dyk-mobile-brand img {
			max-width: 180px;
			max-height: 60px;
			object-fit: contain;
		}
		.dyk-mobile-brand h1 {
			font-size: 24px;
			font-weight: 800;
			color: <?php echo esc_attr($button_color); ?>;
		}

		/* Responsive Breakpoints */
		@media (max-width: 900px) {
			.dyk-brand-side {
				display: none;
			}
			.dyk-form-side {
				flex: 1;
				padding: 36px 20px;
				min-height: 100vh;
			}
			.dyk-mobile-brand {
				display: block;
			}
			.dyk-form-header {
				text-align: center;
			}
		}
	</style>
</head>
<body>
	
	<div class="dyk-split-container">
		
		<!-- LEFT SIDE: Brand Canvas -->
		<div class="dyk-brand-side">
			<div class="dyk-brand-top"></div>

			<div class="dyk-brand-center">
				<?php if(!empty($logo_url)){ ?>
					<img class="dyk-brand-logo-img" src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($app_name); ?>" />
				<?php } else { ?>
					<div class="dyk-brand-title">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
						</svg>
						<span><?php echo esc_html($app_name); ?></span>
					</div>
				<?php } ?>
			</div>

			<div class="dyk-brand-footer">
				&copy; <?php echo date('Y'); ?> <?php echo esc_html($app_name); ?>
			</div>
		</div>

		<!-- RIGHT SIDE: Form Canvas -->
		<div class="dyk-form-side">
			<div class="dyk-form-wrapper">
				
				<!-- Mobile Brand Header (Visible only on mobile) -->
				<div class="dyk-mobile-brand">
					<a href="<?php echo esc_url($home_url); ?>">
						<?php if(!empty($logo_url)){ ?>
							<img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($app_name); ?>" />
						<?php } else { ?>
							<h1><?php echo esc_html($app_name); ?></h1>
						<?php } ?>
					</a>
				</div>

				<div class="dyk-form-header">
					<h2>Login</h2>
				</div>

				<!-- Inline Notice Box -->
				<div class="dyk-notice" id="login-notice" role="alert"></div>

				<form id="dyk-login-form" onsubmit="return false;">
					
					<!-- Email / Username Field with Left Envelope Icon -->
					<div class="dyk-input-group">
						<div class="dyk-input-box">
							<span class="dyk-input-icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<rect width="20" height="16" x="2" y="4" rx="2"/>
									<path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
								</svg>
							</span>
							<input id="email" type="text" class="dyk-form-input" name="email" placeholder="Email" autocomplete="username" autofocus />
						</div>
					</div>

					<!-- Password Field with Left Lock Icon & Right Toggle -->
					<div class="dyk-input-group">
						<div class="dyk-input-box dyk-password-box">
							<span class="dyk-input-icon">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
									<path d="M7 11V7a5 5 0 0 1 10 0v4"/>
								</svg>
							</span>
							<input id="password" type="password" class="dyk-form-input" name="password" placeholder="Password" autocomplete="current-password" />
							<button type="button" class="dyk-password-toggle" id="togglePassword" aria-label="Tampilkan sandi">
								<!-- Eye Open Icon -->
								<svg class="icon-eye-open hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
									<circle cx="12" cy="12" r="3"/>
								</svg>
								<!-- Eye Off Icon -->
								<svg class="icon-eye-closed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
									<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
									<path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
									<path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
									<line x1="2" x2="22" y1="2" y2="22"/>
								</svg>
							</button>
						</div>
					</div>

					<!-- Forgot Password Link -->
					<div class="dyk-forgot-row">
						<?php if($changepass_setting == '1'){ ?>
							<a href="<?php echo esc_url($home_url); ?>/changepass" class="dyk-forgot-link">Forgot Password?</a>
						<?php } ?>
					</div>

					<!-- Submit Button -->
					<div class="dyk-input-group" style="margin-bottom: 0;">
						<button type="submit" class="dyk-btn-submit" id="login_now">
							<span class="dyk-spinner" id="login-spinner"></span>
							<span class="dyk-btn-text">Login</span>
						</button>
					</div>

				</form>

				<!-- Sign Up Link -->
				<?php if($register_setting == '1'){ ?>
				<div class="dyk-signup-row">
					Don't have an account?<a href="<?php echo esc_url($home_url . '/' . $page_register); ?>">Sign Up</a>
				</div>
				<?php } ?>

				<!-- Back to home -->
				<div class="dyk-back-home">
					<a href="<?php echo esc_url($home_url); ?>">&larr; Kembali ke <?php echo esc_html($app_name); ?></a>
				</div>

				<!-- Powered by Footer -->
				<?php if($powered_by_setting == '1'){ ?>
				<div class="dyk-powered-footer">
					<a href="https://donasiyuk.id" target="_blank">Powered by DonasiYuk</a>
				</div>
				<?php } ?>

			</div>
		</div>

	</div>

	<script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/jquery.min.js';?>"></script>
	<script>
	(function($) {
		'use strict';

		function showNotice(message, isSuccess) {
			var $notice = $('#login-notice');
			$notice.removeClass('notice-success');
			if (isSuccess) {
				$notice.addClass('notice-success');
			}
			$notice.html(message).show();
		}

		function hideNotice() {
			$('#login-notice').hide().empty();
		}

		function doLogin() {
			var email = $('#email').val().trim();
			var password = $('#password').val();

			hideNotice();

			if (!email || !password) {
				showNotice('<strong>KESALAHAN</strong>: Silakan isi email/username dan password.', false);
				return false;
			}

			var $btn = $('#login_now');
			var $spinner = $('#login-spinner');
			var $btnText = $btn.find('.dyk-btn-text');

			$btn.prop('disabled', true);
			$spinner.show();
			$btnText.text('Memproses...');

			var data = {
				action: 'dykfunction_login_user',
				datanya: [email, password]
			};

			$.post('<?php echo esc_url($home_url); ?>/wp-admin/admin-ajax.php', data, function(response) {
				$btn.prop('disabled', false);
				$spinner.hide();
				$btnText.text('Login');

				if (response === 'email_failed' || response === 'phone_failed' || response === 'username_failed') {
					showNotice('<strong>KESALAHAN</strong>: Email/nama pengguna atau kata sandi salah.', false);
				} else if (response === 'not_allowed') {
					showNotice('<strong>KESALAHAN</strong>: Login saat ini sedang tidak diizinkan.', false);
				} else {
					showNotice('Login berhasil! Mengalihkan...', true);
					var new_response = "<?php echo esc_url($home_url); ?>" + response;
					window.location.href = new_response;
				}
			}).fail(function() {
				$btn.prop('disabled', false);
				$spinner.hide();
				$btnText.text('Login');
				showNotice('<strong>KESALAHAN</strong>: Terjadi gangguan jaringan. Silakan coba lagi.', false);
			});
		}

		$('#dyk-login-form').on('submit', function(e) {
			e.preventDefault();
			doLogin();
		});

		// Password Toggle
		$('#togglePassword').on('click', function() {
			var $input = $('#password');
			var isPassword = $input.attr('type') === 'password';
			$input.attr('type', isPassword ? 'text' : 'password');

			var $eyeOpen = $(this).find('.icon-eye-open');
			var $eyeClosed = $(this).find('.icon-eye-closed');

			if (isPassword) {
				$eyeOpen.removeClass('hidden');
				$eyeClosed.addClass('hidden');
				$(this).attr('aria-label', 'Sembunyikan sandi');
			} else {
				$eyeOpen.addClass('hidden');
				$eyeClosed.removeClass('hidden');
				$(this).attr('aria-label', 'Tampilkan sandi');
			}
		});

	})(jQuery);
	</script>
</body>
</html>
