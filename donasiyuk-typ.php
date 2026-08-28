<?php

	global $wpdb;
	global $wp;
    $table_name = $wpdb->prefix . "dyk_users";
    $table_name2 = $wpdb->prefix . "dyk_settings";
    $table_name3 = $wpdb->prefix . "dyk_donate";
    $table_name4 = $wpdb->prefix . "dyk_campaign";
    $table_name5 = $wpdb->prefix . "dyk_payment_log"; // additional table for tripay transaction instruction

    // Check IP User
    check_blocked_ip();

	$slug = $donasi_id;
	$campaign = $wpdb->get_results('SELECT * from '.$table_name4.' where slug="'.$slug.'"');
	if($campaign==null){
		wp_redirect( get_site_url() );
		exit;
	}

	// Settings
    $query_settings = $wpdb->get_results('SELECT data from '.$table_name2.' where type="logo_url" or type="app_name" or type="login_setting" or type="login_text" or type="register_setting" or type="register_text" or type="page_login" or type="page_register" or type="theme_color" or type="currency" or type="powered_by_setting" or type="fb_pixel" or type="fb_event" or type="gtm_id" or type="tiktok_pixel" or type="form_confirmation_setting" or type="flip_redirect" or type="metapixel_only" or type="metapixel_convertion" or type="metapixel_convertion_data" or type="flying_button_settings" or type="flying_button_bubble_text" or type="flying_button_message" or type="flying_button_number" or type="flying_button_page_settings"  or type="button_confirmation_setting" or type="button_confirmation_text" or type="button_confirmation_whatsapp" or type="button_confirmation_message" or type="button_confirmation_wa_settings" ORDER BY id ASC');
    $logo_url 		= $query_settings[0]->data;
    $app_name 		= $query_settings[1]->data;
    $login_setting 	= $query_settings[2]->data;
    $login_text 	= $query_settings[3]->data;
    $register_setting = $query_settings[4]->data;
    $register_text 	= $query_settings[5]->data;
    $page_login 	= $query_settings[6]->data;
    $page_register 	= $query_settings[7]->data;
    $general_theme_color = json_decode($query_settings[8]->data, true);
    $currency		= $query_settings[9]->data;
    $powered_by_setting = $query_settings[10]->data;
    $fb_pixel 	 		= $query_settings[11]->data;
    $fb_event  	 		= json_decode($query_settings[12]->data, true);
    $gtm_id             = $query_settings[13]->data;
    $tiktok_pixel       = $query_settings[14]->data;
    $form_confirmation_setting = $query_settings[15]->data;
    $flip_redirect 				= $query_settings[16]->data;
    $metapixel_only             = $query_settings[17]->data;
    $metapixel_convertion       = $query_settings[18]->data;
    $metapixel_convertion_data  = $query_settings[19]->data;
    $flying_button_settings        = $query_settings[20]->data;
    $flying_button_bubble_text     = $query_settings[21]->data;
    $flying_button_message         = $query_settings[22]->data;
    $flying_button_number          = $query_settings[23]->data;
    $flying_button_page_settings   = $query_settings[24]->data;
    $button_confirmation_setting   = $query_settings[25]->data ?? null;
    $button_confirmation_text      = $query_settings[26]->data ?? null;
    $button_confirmation_whatsapp  = $query_settings[27]->data ?? null;
    $button_confirmation_message   = $query_settings[28]->data ?? null;
    $button_confirmation_wa_settings   = $query_settings[29]->data ?? null;

    $flying_button_page_settings  = json_decode($flying_button_page_settings, true);
    if (
        is_array($flying_button_page_settings) &&
        isset($flying_button_page_settings['settings']) &&
        is_array($flying_button_page_settings['settings'])
    ) {
        $page_campaign_button = $flying_button_page_settings['settings'][0] ?? null;
        $page_form_button     = $flying_button_page_settings['settings'][1] ?? null;
        $page_invoice_button  = $flying_button_page_settings['settings'][2] ?? null;
    } else {
        $page_campaign_button = null;
        $page_form_button     = null;
        $page_invoice_button  = null;
    }

    // FB EVENT
    $event_1   	 = $fb_event['event'][0] ?? '';
    $event_2   	 = $fb_event['event'][1] ?? '';
    $event_3   	 = $fb_event['event'][2] ?? '';
    $event_4     = $fb_event['event'][3] ?? '';

    // meta pixel only - general
    if($metapixel_only=='1' || $metapixel_only==null){
        $fb_pixel = $fb_pixel;
    }

    // meta pixel convertion - general
    if($metapixel_convertion=='1'){
        if($metapixel_convertion_data!=''){
            $metapixel_convertion_data = json_decode($metapixel_convertion_data, true);
            $jumlah_pixel = $metapixel_convertion_data['jumlah'] ?? 0;
        }else{
            $jumlah_pixel = 0;
        }
        
        $fb_pixel_convertion = '';
        if($jumlah_pixel>=1){
            $count_pixel = 1;
            foreach ($metapixel_convertion_data['data'] as $key => $value) {
                if($count_pixel==$jumlah_pixel){
                    $fb_pixel_convertion .= $value[0];
                }else{
                    $fb_pixel_convertion .= $value[0].',';
                }
                $count_pixel++;
            }
        }
        $fb_pixel = $fb_pixel_convertion;
    }

	$home_url = get_site_url();
	if($link_code=='campaign'){
		$current_url = $home_url.'/campaign/'.$slug;
	}else{
		$current_url = $home_url.'/preview/'.$slug;
	}

	$donate = $wpdb->get_results('SELECT * from '.$table_name3.' where invoice_id="'.$invoice_id.'"');
	if($donate==null){
		wp_redirect( $current_url );
		exit;
	}

    // CS Button Konfirmasi
    $cs_id = $donate[0]->cs_id;
    $cs_wa_number = '';
    if($button_confirmation_wa_settings=='1'){
        if($cs_id>0){
            $data_cs = $wpdb->get_results('SELECT * from '.$table_name.' where user_id="'.$cs_id.'"');
            if($data_cs!=null){
                if($data_cs[0]->user_wa!=''){
                    $cs_wa_number = $data_cs[0]->user_wa;
                }else{
                    $cs_wa_number = '081';
                }
            }else{
                $cs_wa_number = '08';
            }
            $wa_csnya = $cs_wa_number;
        }else{
            $wa_csnya = $button_confirmation_whatsapp;
        }
    }else{
        $wa_csnya = $button_confirmation_whatsapp;
    }

	// Get DATA
	$total = $donate[0]->nominal;
	if($total>999){
		$total_depan = substr($total, 0, -3);
		$total_depan = number_format($total_depan,0,",",".");
	}else{
		$total_depan = '';
	}
	$total_belakang = substr($total, -3);
	$bank_code = $donate[0]->payment_code;
    if($bank_code=='cimb'){ $bank_code = 'cimb_niaga'; }
	$payment_account = $donate[0]->payment_account;
	$payment_number = $donate[0]->payment_number;
	$payment_qrcode = $donate[0]->payment_qrcode;
	$payment_date = $donate[0]->created_at;
	$sapaan = $donate[0]->sapaan;
    $donatur = $donate[0]->name;
	$img_confirmation_url = $donate[0]->img_confirmation_url;
	$payment_method = $donate[0]->payment_method;
    $unique_code = $donate[0]->unique_nominal ?? 0;

	$title = $campaign[0]->title;
	if($campaign[0]->form_status=='1'){
        $form_text   = json_decode($campaign[0]->form_text, true);
        $text1 = $form_text['text'][0] ?? '';
        $text2 = $form_text['text'][1] ?? 'Donasi';
        $text3 = $form_text['text'][2] ?? '';
        $text4 = $form_text['text'][3] ?? '';

        $donasi_text = $text2;
        if($campaign[0]->form_type=='5'){
	    	$donasi_text = 'Qurban';
	    }
        if($campaign[0]->form_type=='4' || $campaign[0]->form_type=='7'){
	    	$donasi_text = 'Zakat';
	    }
    }else{
    	$donasi_text = 'Donasi';
    }

    $general_status = $campaign[0]->general_status;
    $allocation_title = $campaign[0]->allocation_title;
    $allocation_others_title = $campaign[0]->allocation_others_title;
    if($general_status=='1'){
        if($allocation_title=='1' || $allocation_title=='0'){
            $allocation_title = 'Donasi';
        }elseif($allocation_title=='2'){
            $allocation_title = 'Zakat';
        }elseif($allocation_title=='3'){
            $allocation_title = 'Qurban';
        }elseif($allocation_title=='4'){
            $allocation_title = 'Infaq';
        }elseif($allocation_title=='5'){
            $allocation_title = 'Wakaf';
        }else{
            $allocation_title = $allocation_others_title;
        }
    }else{
        $allocation_title = 'Donasi';
    }

    // FB EVENT from Campaign
    if($campaign[0]->pixel_status=='1'){
	    $fb_event  = json_decode($campaign[0]->fb_event, true);
	    $event_1   = $fb_event['event'][0] ?? '';
	    $event_2   = $fb_event['event'][1] ?? '';
	    $event_3   = $fb_event['event'][2] ?? '';
	    if(isset($fb_event['event'][3])){
	        $event_4  = $fb_event['event'][3];
	    }
	}

    // GET PIXEL FROM CAMPAIGN
    if($campaign[0]->pixel_status=='1' && $campaign[0]->metapixel_only=='1' ){
    	$fb_pixel  = $campaign[0]->fb_pixel;
    }
    if($campaign[0]->pixel_status=='1' && $campaign[0]->metapixel_only==null){
        if (!empty($row->fb_pixel)){
            $fb_pixel  = $row->fb_pixel;
        }
    }

    // meta pixel and conversion
    if($campaign[0]->pixel_status=='1' && $campaign[0]->metapixel_convertion=='1'){
        if($campaign[0]->metapixel_convertion_data!=''){
            $metapixel_convertion_data = json_decode($campaign[0]->metapixel_convertion_data, true);
            $jumlah_pixel = $metapixel_convertion_data['jumlah'] ?? 0;
        }else{
            $jumlah_pixel = 0;
        }
        
        $fb_pixel_convertion = '';
        if($jumlah_pixel>=1){
            $count_pixel = 1;
            foreach ($metapixel_convertion_data['data'] as $key => $value) {
                if($count_pixel==$jumlah_pixel){
                    $fb_pixel_convertion .= $value[0];
                }else{
                    $fb_pixel_convertion .= $value[0].',';
                }
                $count_pixel++;
            }
        }
        $fb_pixel = $fb_pixel_convertion;
    }

    if($campaign[0]->gtm_status=='1'){
    	$gtm_id  = $campaign[0]->gtm_id;
    }
    if($campaign[0]->tiktok_status=='1'){
    	$tiktok_pixel  = $campaign[0]->tiktok_pixel;
    }

    $theme_color 		= !empty($general_theme_color['color'][0]) ? $general_theme_color['color'][0] : '#10a8e5';
	$progressbar_color  = !empty($general_theme_color['color'][1]) ? $general_theme_color['color'][1] : '#10a8e5';
	$button_color 		= !empty($general_theme_color['color'][2]) ? $general_theme_color['color'][2] : '#10a8e5';

	if(empty($button_color) || $button_color=='#0099ff' || $button_color=='#7680ff' || $button_color=='#7680FF'){
		$button_color = '#10a8e5';
	}
	if(empty($progressbar_color) || $progressbar_color=='#0099ff' || $progressbar_color=='#009F61' || $progressbar_color=='#24CC63'){
		$progressbar_color = '#10a8e5';
	}

    $hex = $button_color;
	list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
	$colornya = 'rgba('.$r.','.$g.','.$b.', 0.05)';
	$color_hovernya = 'rgba('.$r.','.$g.','.$b.', 0.15)';

	$id_login = wp_get_current_user()->ID;

	$data_field = array();
    $data_field[ '{payment_account}' ] = '<span style="color:'.$button_color.'">'.$payment_account.'</span>';
    $data_field[ '{payment_number}' ] = '<span style="color:'.$button_color.'">'.$payment_number.'</span>';
    $data_field[ '{nominal}' ] = '<span style="color:'.$button_color.'">'.$total.'</span>';

	if($form_confirmation_setting=='1'){
		$form_konfirmasi = true;
	}elseif($form_confirmation_setting=='2'){
		if($payment_method=='transfer'){
			$form_konfirmasi = true;
		}else{
			$form_konfirmasi = false;
		}
	}else{
		$form_konfirmasi = false;
	}

	// Currency
    $query_currency = $wpdb->get_results('SELECT data from '.$table_name2.' where type="currency"  ORDER BY id ASC');
    $currency = $query_currency[0]->data ?? 'IDR';
    $lang = get_data_lang($currency);
    $langArray = require_once(ROOTDIR_DYK . 'library/locale/'.$lang.'.php');
    
    $show_currency = donasiyuk_currency($currency);
    $show_currency2 = donasiyuk_currency2($currency);

    // custom whatsapp flying button
    $flying_button_status = $campaign[0]->flying_button_status;
    if($flying_button_status=='1'){
        $flying_button_settings        = $campaign[0]->flying_button_settings;
        $flying_button_bubble_text     = $campaign[0]->flying_button_bubble_text;
        $flying_button_message         = $campaign[0]->flying_button_message;
        $flying_button_number          = $campaign[0]->flying_button_number;
        $flying_button_page_settings   = $campaign[0]->flying_button_page_settings;

        $flying_button_page_settings  = json_decode($flying_button_page_settings, true);
        if (
            is_array($flying_button_page_settings) &&
            isset($flying_button_page_settings['settings']) &&
            is_array($flying_button_page_settings['settings'])
        ) {
            $page_campaign_button = $flying_button_page_settings['settings'][0] ?? null;
            $page_form_button     = $flying_button_page_settings['settings'][1] ?? null;
            $page_invoice_button  = $flying_button_page_settings['settings'][2] ?? null;
        } else {
            $page_campaign_button = null;
            $page_form_button     = null;
            $page_invoice_button  = null;
        }
    }

    // Button Konfirmasi Whatsapp CS
    $chat_admin_message = 'Nama: *'.$donatur.'*
Program: '.$title.'
Jumlah Donasi: '.$show_currency.number_format($total,0,",",".").'
Invoice ID: '.$invoice_id.'

'.$button_confirmation_message.'
';
    $chat_admin_phone = djaPhoneFormat($wa_csnya,$currency);

    // Calculate Expiry Date and Timestamp for live countdown
    $wib = ($currency=='IDR') ? ' WIB' : '';
    $expired_time_raw = donate_expired_time($donate[0]->id, $donate[0]->payment_gateway);
    if(!empty($expired_time_raw)){
        $expired_timestamp = strtotime($expired_time_raw);
        if(!$expired_timestamp){
            $expired_timestamp = strtotime('+24 hour', strtotime($payment_date));
        }
    }else{
        $expired_timestamp = strtotime('+24 hour', strtotime($payment_date));
    }

    $days_id = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
    $months_id = array(
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    );
    $exp_day_name = $days_id[date('w', $expired_timestamp)];
    $exp_day = date('d', $expired_timestamp);
    $exp_month_name = $months_id[(int)date('n', $expired_timestamp)];
    $exp_year = date('Y', $expired_timestamp);
    $exp_time = date('H:i', $expired_timestamp);
    $expired_display = $exp_day_name . ', ' . $exp_day . ' ' . $exp_month_name . ' ' . $exp_year . ' ' . $exp_time . $wib;

    // Detect Payment Method Title, Label, and Copy Value
    $payment_acc_lower = strtolower((string)($payment_account ?? ''));
    $bank_code_lower   = strtolower((string)($bank_code ?? ''));
    $payment_qr_str    = (string)($payment_qrcode ?? '');
    $payment_num_str   = (string)($payment_number ?? '');

    $is_va = ($payment_method=='va' || strpos($payment_acc_lower, 'virtual account') !== false || strpos($bank_code_lower, 'va') !== false);
    $is_qris = ($bank_code_lower=='qris' || $payment_acc_lower=='qris - remitcepat' || $payment_acc_lower=='qris - duitku' || (!empty($donate[0]->payment_gateway) && $donate[0]->payment_gateway=='duitku' && ($bank_code_lower=='qris' || !empty($payment_qrcode))) || (!empty($payment_qr_str) && (strpos($payment_qr_str, 'midtrans.com') !== false || strpos($payment_qr_str, 'ipaymu') !== false)));

    if($is_va){
        $method_title = !empty($payment_account) && $payment_account!='0' ? $payment_account : strtoupper((string)$bank_code).' Virtual Account';
        $number_label = 'Nomor Virtual Account';
        $number_to_copy = !empty($payment_num_str) ? preg_replace('/[^\d]/', '', $payment_num_str) : preg_replace('/[^\d]/', '', (string)$payment_account);
    }elseif($is_qris){
        $method_title = 'QRIS';
        $number_label = 'Kode Pembayaran / QRIS';
        $number_to_copy = (!empty($payment_num_str) && strtolower($payment_num_str) != 'qris' && strpos($payment_num_str, 'http') === false) ? $payment_num_str : '';
    }elseif(in_array($bank_code_lower, array('gopay', 'ovo', 'dana', 'shopeepay', 'linkaja'))){
        $method_title = ucfirst((string)$bank_code);
        $number_label = 'Nomor Pembayaran';
        $number_to_copy = (!empty($payment_num_str) && strtolower($payment_num_str) != $bank_code_lower && strpos($payment_num_str, 'http') === false) ? $payment_num_str : '';
    }elseif($bank_code_lower=='cc'){
        $method_title = 'Kartu Kredit / Debit';
        $number_label = 'Pembayaran Online';
        $number_to_copy = '';
    }elseif($bank_code_lower=='tunai'){
        $method_title = 'Tunai / Cash';
        $number_label = 'Metode Tunai';
        $number_to_copy = '';
    }elseif($bank_code_lower=='jemput_donasi'){
        $method_title = 'Jemput Donasi';
        $number_label = 'Alamat & Kontak';
        $number_to_copy = '';
    }else{
        $method_title = !empty($payment_account) ? $payment_account : 'Bank ' . strtoupper((string)$bank_code);
        $number_label = 'Nomor Rekening';
        $number_to_copy = !empty($payment_num_str) ? preg_replace('/[^\d]/', '', $payment_num_str) : preg_replace('/[^\d]/', '', (string)$payment_account);
    }

    // Automatically hide proof upload form for automated payment gateways
    $is_pg_automated = (!empty($donate[0]->payment_gateway) || in_array($payment_acc_lower, array('duitku', 'tripay', 'midtrans', 'ipaymu', 'flip', 'remitcepat', 'xendit', 'qris - duitku', 'qris - remitcepat')) || $is_qris);
    if($is_pg_automated){
        $form_konfirmasi = false;
    }

    // Bank Logo Path
    $bank_logo_file = plugin_dir_path( __FILE__ ) . 'assets/images/bank/' . $bank_code . '.png';
    if(file_exists($bank_logo_file)){
        $bank_logo_url = plugin_dir_url( __FILE__ ) . 'assets/images/bank/' . $bank_code . '.png';
    }else{
        $bank_logo_url = plugin_dir_url( __FILE__ ) . 'assets/images/bank/bank.png';
    }

    $formatted_total = $show_currency . number_format($total, 0, ',', '.');
    $formatted_subtotal = $show_currency . number_format($total - $unique_code, 0, ',', '.');
    $formatted_unique = $show_currency . number_format($unique_code, 0, ',', '.');

?>
<!-- Powered by DonasiYuk.id -->
<!DOCTYPE html>
<html lang="id">
<head>
    <?php if($donate[0]->status=='1') { ?> 
	<title><?php echo get_langArray('f_typ_desc6'); ?> - <?php echo $app_name; ?></title>
    <?php } else { ?>
    <title><?php echo get_langArray('f_typ_desc5'); ?> - <?php echo $app_name; ?></title>
    <?php } ?>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=0">
	<meta name="application-name" content="<?php echo $home_url; ?>"/>
	<meta property="og:url" content="<?php echo $home_url; ?>" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="<?php echo get_langArray('f_typ_desc5'); ?> - <?php echo $app_name; ?>" />
	<meta property="og:description" content="<?php echo $app_name; ?>" />
	<meta property="og:image" content="<?php echo $logo_url; ?>" />
	<?php dyk_set_favicon(); ?>
	<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ) . 'assets/css/donasiyuk.css';?>">
	<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ) . 'assets/css/donasiyuk-style.css';?>">
	<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ) . 'assets/css/lucide-fix.css';?>">
    <link href="<?php echo plugin_dir_url( __FILE__ ); ?>admin/plugins/sweet-alert2/sweetalert2.min.css" rel="stylesheet" type="text/css">
    <link href="<?php echo plugin_dir_url( __FILE__ ); ?>admin/plugins/animate/animate-4.1.1.min.css" rel="stylesheet" type="text/css">
    <script src="<?php echo plugin_dir_url( __FILE__ ); ?>admin/plugins/sweet-alert2/sweetalert2.min.js"></script>

	<style type="text/css">
        * { box-sizing: border-box; }
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f4f6f8 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            color: #1e293b !important;
            -webkit-font-smoothing: antialiased !important;
            width: 100% !important;
            max-width: 100% !important;
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: flex-start !important;
        }

        .dyk-invoice-wrapper {
            width: 100% !important;
            max-width: 480px !important;
            margin: 0 auto !important;
            min-height: 100vh !important;
            background: #f4f6f8 !important;
            padding-bottom: 40px !important;
            position: relative !important;
            box-sizing: border-box !important;
        }

        /* --- Top Navigation Header --- */
        .dyk-invoice-nav {
            background: #ffffff;
            height: 52px;
            display: flex;
            align-items: center;
            padding: 0 16px;
            border-bottom: 1px solid #f1f5f9;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .dyk-invoice-nav-back {
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 8px;
            margin-left: -8px;
            margin-right: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.15s ease;
            text-decoration: none;
        }
        .dyk-invoice-nav-back:hover {
            background: #f1f5f9;
        }
        .dyk-invoice-nav-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.2px;
        }

        .dyk-invoice-content {
            padding: 16px;
        }

        /* --- Top Blue Countdown Card --- */
        .dyk-countdown-banner {
            background: #1ba8f0;
            background: linear-gradient(135deg, #1ba8f0 0%, #0099ff 100%);
            border-radius: 12px 12px 0 0;
            padding: 16px 20px;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dyk-countdown-left {
            text-align: left;
        }
        .dyk-countdown-label {
            font-size: 12px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.92);
            margin-bottom: 3px;
        }
        .dyk-countdown-date {
            font-size: 13.5px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.3;
        }
        .dyk-countdown-right {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            white-space: nowrap;
            padding-left: 10px;
        }
        .dyk-countdown-right svg {
            width: 17px;
            height: 17px;
            stroke: #ffffff;
            stroke-width: 2.2;
        }

        /* --- Main Payment Details Card --- */
        .dyk-main-card {
            background: #ffffff;
            border-radius: 0 0 12px 12px;
            padding: 20px 18px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef2f6;
            border-top: none;
            margin-bottom: 16px;
        }

        .dyk-method-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .dyk-method-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            text-align: left;
        }
        .dyk-bank-logo-wrap {
            max-height: 28px;
            display: flex;
            align-items: center;
        }
        .dyk-bank-logo-wrap img {
            max-height: 26px;
            max-width: 90px;
            object-fit: contain;
            display: block;
        }

        /* --- Account / VA Number Container Box --- */
        .dyk-number-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .dyk-number-info {
            text-align: left;
        }
        .dyk-number-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 2px;
        }
        .dyk-number-value {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            line-height: 1.2;
            word-break: break-all;
        }
        .dyk-btn-salin {
            border: 1.5px solid #0099ff;
            background: #ffffff;
            color: #0099ff;
            font-weight: 600;
            font-size: 13px;
            padding: 6px 18px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
            outline: none;
        }
        .dyk-btn-salin:hover {
            background: #0099ff;
            color: #ffffff;
        }
        .dyk-btn-salin:active {
            transform: scale(0.95);
        }

        /* --- QR Code Display --- */
        .dyk-qr-box {
            text-align: center;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 18px;
            margin-bottom: 16px;
        }
        .dyk-qr-box img {
            max-width: 220px;
            height: auto;
            margin: 0 auto 10px auto;
            display: block;
        }
        .dyk-qr-hint {
            font-size: 12.5px;
            color: #64748b;
            margin: 0;
        }

        /* --- Total Donasi Accordion --- */
        .dyk-total-section {
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            padding: 12px 0;
            margin-bottom: 18px;
        }
        .dyk-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }
        .dyk-total-label {
            font-size: 14px;
            color: #475569;
            font-weight: 500;
        }
        .dyk-total-right {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .dyk-total-amount {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }
        .dyk-total-chevron {
            width: 18px;
            height: 18px;
            color: #64748b;
            transition: transform 0.2s ease;
        }
        .dyk-total-chevron.open {
            transform: rotate(180deg);
        }
        .dyk-total-breakdown {
            display: block;
            padding-top: 12px;
            margin-top: 10px;
            border-top: 1px dashed #e2e8f0;
            font-size: 13px;
        }
        .dyk-breakdown-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            color: #64748b;
        }
        .dyk-breakdown-row.total-row {
            font-weight: 700;
            color: #0f172a;
            border-top: 1px solid #f1f5f9;
            padding-top: 6px;
            margin-top: 6px;
        }

        /* --- Primary Action Button --- */
        .dyk-btn-check-status {
            width: 100%;
            background: #00a5e8;
            background: linear-gradient(135deg, #00a5e8 0%, #0099ff 100%);
            color: #ffffff;
            font-size: 15px;
            font-weight: 700;
            padding: 13px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-align: center;
            margin-bottom: 12px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 153, 255, 0.2);
            outline: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }
        .dyk-btn-check-status:hover {
            background: #0088e0;
        }
        .dyk-btn-check-status:active {
            transform: scale(0.98);
        }

        /* --- Instructions Accordion --- */
        .dyk-instructions-card {
            border-top: 1px solid #f1f5f9;
            padding-top: 14px;
        }
        .dyk-instructions-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
            padding: 4px 0;
        }
        .dyk-instructions-title {
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin: 0;
        }
        .dyk-instructions-arrow {
            width: 18px;
            height: 18px;
            color: #94a3b8;
            transition: transform 0.2s ease;
        }
        .dyk-instructions-arrow.open {
            transform: rotate(90deg);
        }
        .dyk-instructions-content {
            display: none;
            padding-top: 14px;
            text-align: left;
        }
        .dyk-instruction-step-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 8px;
            overflow: hidden;
        }
        .dyk-instruction-step-header {
            padding: 10px 14px;
            font-size: 13.5px;
            font-weight: 600;
            color: #1e293b;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
        }
        .dyk-instruction-step-header.active {
            background: #edf7ff;
            color: #0099ff;
        }
        .dyk-instruction-step-header svg {
            transition: transform 0.2s ease;
        }
        .dyk-instruction-step-header.active svg {
            transform: rotate(180deg);
        }
        .dyk-instruction-step-body {
            display: none;
            padding: 10px 14px 14px 28px;
            background: #ffffff;
            border-top: 1px solid #f1f5f9;
            font-size: 13px;
            color: #475569;
            line-height: 1.6;
        }
        .dyk-instruction-step-body ol {
            margin: 0;
            padding-left: 16px;
        }
        .dyk-instruction-step-body li {
            margin-bottom: 6px;
        }



        /* Primary Orange Button */
        .dyk-btn-install {
            width: 100%;
            background: #eb6b34;
            background: linear-gradient(135deg, #f27238 0%, #e05e22 100%);
            color: #ffffff !important;
            font-size: 14.5px;
            font-weight: 700;
            padding: 12px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: block;
            text-decoration: none !important;
            text-align: center;
            box-sizing: border-box;
            margin-bottom: 10px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(235, 107, 52, 0.25);
        }
        .dyk-btn-install:hover {
            background: #d9551a;
            color: #ffffff !important;
        }
        .dyk-btn-install:active {
            transform: scale(0.98);
        }

        /* Secondary Outline Blue Button */
        .dyk-btn-other-campaign {
            width: 100%;
            border: 1.5px solid #0099ff;
            background: transparent;
            color: #0099ff !important;
            font-size: 14.5px;
            font-weight: 700;
            padding: 11px;
            border-radius: 8px;
            cursor: pointer;
            display: block;
            text-decoration: none !important;
            text-align: center;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }
        .dyk-btn-other-campaign:hover {
            background: #edf7ff;
        }
        .dyk-btn-other-campaign:active {
            transform: scale(0.98);
        }

        /* --- Success Screen Styling --- */
        .dyk-success-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 36px 20px;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            margin-top: 16px;
        }
        .dyk-success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ecfdf5;
            border-radius: 50%;
            color: #10b981;
        }
        .dyk-success-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 10px 0;
        }
        .dyk-success-desc {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
            margin: 0 0 24px 0;
        }

        /* --- Upload & Proof Confirmation (if enabled) --- */
        .dyk-upload-section {
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 10px;
            padding: 18px;
            text-align: center;
            margin: 16px 0;
        }
        .dyk-upload-btn {
            background: #1e293b;
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-block;
            margin-top: 8px;
        }

        /* --- WhatsApp Confirmation Button --- */
        .dyk-btn-wa-confirm {
            background: #25D366;
            color: #ffffff !important;
            font-size: 14.5px;
            font-weight: 700;
            padding: 12px;
            border-radius: 8px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none !important;
            margin-bottom: 12px;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
        }

        .dyk-footer-powered {
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            margin-top: 20px;
        }
        .dyk-footer-powered a {
            color: #64748b;
            text-decoration: none;
        }

        /* Sweetalert Customization */
        .swal2-popup { border-radius: 14px !important; font-family: inherit !important; }
	</style>

	<?php if (!empty($fb_pixel) && strpos($fb_pixel, ',') !== false ) {
        $array_pixel  = explode(",", $fb_pixel);
    ?>
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    <?php foreach ($array_pixel as $values){ ?> 
    fbq('init', '<?php echo $values; ?>');
    <?php } ?>
    <?php if($donate[0]->status=='1') { ?>
    fbq('track', '<?php echo $event_4; ?>', { value: <?php echo $total; ?>, currency: '<?php echo $currency?>' });
    <?php }else{ ?>
    fbq('track', '<?php echo $event_3; ?>', { value: <?php echo $total; ?>, currency: '<?php echo $currency?>' });
    <?php } ?>
    </script>
    <?php } elseif(!empty($fb_pixel)) { ?>
	<script>
	!function(f,b,e,v,n,t,s)
	{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
	n.callMethod.apply(n,arguments):n.queue.push(arguments)};
	if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
	n.queue=[];t=b.createElement(e);t.async=!0;
	t.src=v;s=b.getElementsByTagName(e)[0];
	s.parentNode.insertBefore(t,s)}(window, document,'script',
	'https://connect.facebook.net/en_US/fbevents.js');
	fbq('init', '<?php echo $fb_pixel; ?>');
	<?php if($donate[0]->status=='1') { ?>
	fbq('track', '<?php echo $event_4; ?>', { value: <?php echo $total; ?>, currency: '<?php echo $currency?>' });
	<?php }else{ ?>
	fbq('track', '<?php echo $event_3; ?>', { value: <?php echo $total; ?>, currency: '<?php echo $currency?>' });
	<?php } ?>
	</script>
    <?php } ?>

	<?php if($gtm_id!=''){ ?>
    <script>
      var ptag=<?php echo $total;?>;
      var utag="<?php echo d_randomString(20);?>";
      dataLayer = [{ 'purchase': ptag, 'uuid': utag }];
    </script>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?php echo $gtm_id;?>');</script>
    <?php } ?>

    <?php if($tiktok_pixel!=''){ ?>
    <script>
    !function (w, d, t) {
      w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
      ttq.load('<?php echo $tiktok_pixel; ?>');
      ttq.page();
      <?php if($donate[0]->status=='1') { ?> 
        ttq.track('<?php echo $event_4; ?>', { content_id: '<?php echo $campaign[0]->campaign_id; ?>', content_type: 'product', content_name: '<?php echo str_replace("'", "", $campaign[0]->title); ?>', value: <?php echo $total;?>, currency: '<?php echo $currency?>' });
      <?php }else { ?>
        ttq.track('<?php echo $event_3; ?>', { content_id: '<?php echo $campaign[0]->campaign_id; ?>', content_type: 'product', content_name: '<?php echo str_replace("'", "", $campaign[0]->title); ?>', value: <?php echo $total;?>, currency: '<?php echo $currency?>' });
      <?php } ?>
    }(window, document, 'ttq');
    </script>
    <?php } ?>

</head>
<body>
    <div class="dyk-invoice-wrapper">

        <!-- Top Navigation Header -->
        <header class="dyk-invoice-nav">
            <a href="javascript:;" onclick="if(history.length > 1){history.back();}else{window.location.href='<?php echo $current_url; ?>';}" class="dyk-invoice-nav-back" title="Kembali">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0f172a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <h1 class="dyk-invoice-nav-title"><?php echo ($donate[0]->status=='1') ? 'Pembayaran Berhasil' : 'Instruksi Pembayaran'; ?></h1>
        </header>

        <div class="dyk-invoice-content">

        <?php if($donate[0]->status=='1') { ?> 
            <!-- ================= SUCCESS VIEW ================= -->
            <div class="dyk-success-card">
                <div class="dyk-success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <h2 class="dyk-success-title">Alhamdulillah, Donasi Diterima!</h2>
                <p class="dyk-success-desc">
                    Terima kasih <b><?php echo $sapaan; ?> <?php echo str_replace('\\', '', $donatur); ?></b>.<br>
                    <?php echo $allocation_title; ?> Anda sebesar <b><?php echo $formatted_total; ?></b> pada program <b><?php echo $title; ?></b> telah kami terima dengan baik.
                </p>

                <a href="<?php echo $home_url; ?>" class="dyk-btn-install" style="background:#10b981;margin-bottom:10px;">Kembali ke Beranda</a>
                <a href="<?php echo $current_url; ?>" class="dyk-btn-other-campaign">Lihat Detail Program</a>
            </div>

        <?php } else { ?>
            <!-- ================= PENDING INVOICE VIEW ================= -->

            <!-- 1. Top Blue Countdown Card -->
            <div class="dyk-countdown-banner">
                <div class="dyk-countdown-left">
                    <div class="dyk-countdown-label">Batas waktu pembayaran</div>
                    <div class="dyk-countdown-date"><?php echo $expired_display; ?></div>
                </div>
                <div class="dyk-countdown-right">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span id="countdown-timer">00:00:00</span>
                </div>
            </div>

            <!-- 2. Main Payment Details Card -->
            <div class="dyk-main-card">
                <!-- Method Name & Bank Logo -->
                <div class="dyk-method-header">
                    <h2 class="dyk-method-title"><?php echo $method_title; ?></h2>
                    <div class="dyk-bank-logo-wrap">
                        <img src="<?php echo $bank_logo_url; ?>" alt="<?php echo $bank_code; ?>">
                    </div>
                </div>

                <!-- Virtual Account / Rekening Box (Only when valid number exists and not QRIS) -->
                <?php 
                    $show_number_box = (!$is_qris && !empty($number_to_copy) && strtolower($number_to_copy) != 'qris' && strtolower($number_to_copy) != strtolower($bank_code));
                    if($show_number_box) { 
                ?>
                <div class="dyk-number-box">
                    <div class="dyk-number-info">
                        <div class="dyk-number-label"><?php echo $number_label; ?></div>
                        <div class="dyk-number-value" id="display-number-val"><?php echo $number_to_copy; ?></div>
                    </div>
                    <?php if(!empty($number_to_copy)) { ?>
                    <button type="button" class="dyk-btn-salin btn-copy-action" data-salin="<?php echo $number_to_copy; ?>">Salin</button>
                    <?php } ?>
                </div>
                <?php } ?>

                <!-- QR Code (if QRIS / iPaymu / Midtrans) -->
                <?php 
                    $from_ipaymu = false;
                    $from_flip = false;
                    $ipaymuLink = !empty($payment_qrcode) && strpos($payment_qrcode, 'ipaymu') !== false;
                    $flipLink = !empty($payment_number) && strpos($payment_number, 'flip') !== false;

                    if($ipaymuLink){
                        $content = @file_get_contents($payment_qrcode);
                        if ($content && preg_match("/src=[\"\'][^\'\']+[\"\']/", $content, $matches)) {
                            $payment_qrcode = $matches[0];
                        }
                        $from_ipaymu = true;
                    }
                    if($flipLink){
                        $from_flip = true;
                    }
                ?>

                <?php if($from_ipaymu==true) { ?>
                    <div class="dyk-qr-box">
                        <img <?php echo $payment_qrcode; ?> alt="QR Code">
                        <p class="dyk-qr-hint" style="margin-bottom:4px;">Scan QR-Code menggunakan aplikasi e-wallet / mobile banking</p>
                        <div style="font-size:11.5px;color:#475569;background:#f8fafc;border:1px dashed #cbd5e1;padding:6px 12px;border-radius:6px;display:inline-block;margin-top:4px;">
                            💡 <b>Tips 1 HP:</b> Screenshot QR ini lalu buka menu <b>Scan &gt; Upload Galeri</b> di aplikasi e-wallet/m-banking Anda.
                        </div>
                    </div>
                <?php } elseif($payment_account=='QRIS - Remitcepat' && !empty($payment_number)) { ?>
                    <div class="dyk-qr-box">
                        <div id="qr-code-remitcepat" style="display:flex;justify-content:center;margin-bottom:10px;"></div>
                        <p class="dyk-qr-hint" style="margin-bottom:4px;">Scan QR-Code menggunakan aplikasi e-wallet / mobile banking</p>
                        <div style="font-size:11.5px;color:#475569;background:#f8fafc;border:1px dashed #cbd5e1;padding:6px 12px;border-radius:6px;display:inline-block;margin-top:4px;">
                            💡 <b>Tips 1 HP:</b> Screenshot QR ini lalu buka menu <b>Scan &gt; Upload Galeri</b> di aplikasi e-wallet/m-banking Anda.
                        </div>
                    </div>
                <?php } elseif(!empty($payment_qrcode) && (strpos($payment_qrcode, 'http://') === 0 || strpos($payment_qrcode, 'https://') === 0)) { ?>
                    <div class="dyk-qr-box">
                        <img src="<?php echo esc_url($payment_qrcode); ?>" alt="QRIS Code">
                        <p class="dyk-qr-hint" style="margin-bottom:4px;">Scan QR-Code menggunakan aplikasi e-wallet / mobile banking</p>
                        <div style="font-size:11.5px;color:#475569;background:#f8fafc;border:1px dashed #cbd5e1;padding:6px 12px;border-radius:6px;display:inline-block;margin-top:4px;">
                            💡 <b>Tips 1 HP:</b> Screenshot QR ini lalu buka menu <b>Scan &gt; Upload Galeri</b> di aplikasi e-wallet/m-banking Anda.
                        </div>
                    </div>
                <?php } elseif($is_qris && !empty($payment_qrcode)) { ?>
                    <div class="dyk-qr-box">
                        <div id="qr-code-dynamic" style="display:flex;justify-content:center;margin-bottom:10px;"></div>
                        <p class="dyk-qr-hint" style="margin-bottom:4px;">Scan QR-Code menggunakan aplikasi e-wallet / mobile banking</p>
                        <div style="font-size:11.5px;color:#475569;background:#f8fafc;border:1px dashed #cbd5e1;padding:6px 12px;border-radius:6px;display:inline-block;margin-top:4px;">
                            💡 <b>Tips 1 HP:</b> Screenshot QR ini lalu buka menu <b>Scan &gt; Upload Galeri</b> di aplikasi e-wallet/m-banking Anda.
                        </div>
                    </div>
                <?php } ?>

                <?php if(!empty($donate[0]->deeplink_url) && $bank_code != 'cc' && !$is_qris && !$is_va) { ?>
                    <div style="margin-top:14px;margin-bottom:14px;">
                        <a href="<?php echo esc_url($donate[0]->deeplink_url); ?>" target="_blank" class="dyk-btn-install" style="background:#0099ff;color:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;padding:12px;border-radius:8px;font-weight:700;font-size:14px;box-shadow:0 4px 12px rgba(0,153,255,0.25);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                            Bayar Sekarang (Buka Pembayaran)
                        </a>
                    </div>
                <?php } ?>


                <!-- Total Donasi Row (Accordion) -->
                <div class="dyk-total-section">
                    <div class="dyk-total-row" id="toggle-total-detail">
                        <div class="dyk-total-label">Total donasi</div>
                        <div class="dyk-total-right">
                            <span class="dyk-total-amount"><?php echo $formatted_total; ?></span>
                            <svg class="dyk-total-chevron open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                    </div>
                    <div class="dyk-total-breakdown" id="dyk-total-breakdown">
                        <div class="dyk-breakdown-row">
                            <span>Nominal Donasi</span>
                            <span><?php echo $formatted_subtotal; ?></span>
                        </div>
                        <?php if($unique_code > 0){ ?>
                        <div class="dyk-breakdown-row">
                            <span>Kode Unik</span>
                            <span><?php echo $formatted_unique; ?></span>
                        </div>
                        <?php } ?>
                        <div class="dyk-breakdown-row total-row">
                            <span>Total Transfer</span>
                            <span style="color:#0099ff;"><?php echo $formatted_total; ?></span>
                        </div>
                        <div style="text-align:right;margin-top:8px;">
                            <button type="button" class="dyk-btn-salin btn-copy-action" data-salin="<?php echo $total; ?>" style="font-size:11.5px;padding:4px 14px;">Salin Total</button>
                        </div>
                    </div>
                </div>

                <!-- Flip Redirect Link if any -->
                <?php if (!empty($payment_number) && strpos($payment_number, 'flip.id') !== false) { ?>
				    <div style="margin-bottom: 16px;">
                        <a href="<?php echo $payment_number; ?>" target="_self" style="text-decoration:none;">
                            <div class="dyk-btn-check-status" style="background:#fd6542;">Lanjutkan Pembayaran via Flip &rarr;</div>
                        </a>
                    </div>
				<?php } ?>

                <!-- Action Button: "Cek status pembayaran" -->
                <button type="button" class="dyk-btn-check-status" id="btn-manual-check">
                    <span id="btn-check-text">Cek status pembayaran</span>
                </button>

                <!-- WhatsApp Confirmation Button (if enabled) -->
                <?php if($button_confirmation_setting=='1' && !empty($button_confirmation_whatsapp)){ 
                    if(empty($button_confirmation_text)){ $button_confirmation_text = 'Konfirmasi Pembayaran via WhatsApp'; }
                ?>
                    <a href="https://api.whatsapp.com/send?phone=<?php echo $chat_admin_phone; ?>&text=<?php echo urlencode($chat_admin_message); ?>" target="_blank" class="dyk-btn-wa-confirm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="#ffffff"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span><?php echo $button_confirmation_text; ?></span>
                    </a>
                <?php } ?>

                <!-- Proof of Payment Upload Form (if enabled) -->
                <?php if($form_konfirmasi==true){ ?>
                    <div class="dyk-upload-section" id="upload-proof-box">
                        <div style="font-size:13.5px;font-weight:600;color:#1e293b;margin-bottom:4px;">Upload Bukti Transfer</div>
                        <div style="font-size:12px;color:#64748b;margin-bottom:10px;">Format file didukung: JPG, JPEG, PNG</div>
                        
                        <input type="file" accept=".jpg,.jpeg,.png" id="dyk-file-input" style="display:none;">
                        <button type="button" class="dyk-upload-btn" id="btn-select-proof">Pilih Foto Bukti Transfer</button>
                        
                        <div id="dyk-proof-preview-wrap" style="display:none;margin-top:12px;">
                            <img id="dyk-proof-preview" src="" style="max-height:140px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);display:inline-block;">
                            <div style="margin-top:8px;">
                                <button type="button" class="dyk-btn-check-status" id="btn-submit-proof" style="display:inline-flex;width:auto;padding:8px 20px;font-size:13px;background:#10b981;">Kirim Bukti Transfer</button>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <!-- Instructions Accordion ("Cara pembayaran") -->
                <div class="dyk-instructions-card">
                    <div class="dyk-instructions-toggle" id="toggle-instructions">
                        <h3 class="dyk-instructions-title">Cara pembayaran</h3>
                        <svg class="dyk-instructions-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>

                    <div class="dyk-instructions-content" id="dyk-instructions-content">
                        <?php 
                        // Load Instructions JSON
                        $url = plugin_dir_url( __FILE__ )."library/instructions.json";
                        $curl = curl_init();
                        curl_setopt_array($curl, [
                            CURLOPT_URL => $url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_TIMEOUT => 15,
                            CURLOPT_SSL_VERIFYHOST => 0,
                            CURLOPT_SSL_VERIFYPEER => 0,
                        ]);
                        $response = curl_exec($curl);
                        curl_close($curl);
                        $array = json_decode($response, true);

                        // Tripay instructions if available
                        if($donate[0]->payment_gateway=='tripay'){
                            $id_donate = $donate[0]->id;
                            $payment_log = $wpdb->get_results('SELECT * from '.$table_name5.' where id_donate="'.$id_donate.'"');
                            if (!empty($payment_log) && isset($payment_log[0])) {
                                $hasil = $payment_log[0]->log;
                                $data_log = json_decode($hasil);
                                if (isset($data_log->data->instructions) && is_array($data_log->data->instructions)) {
                                    foreach ($data_log->data->instructions as $val_inst) {
                                        echo '<div class="dyk-instruction-step-item">';
                                        echo '<div class="dyk-instruction-step-header"><span>'.$val_inst->title.'</span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg></div>';
                                        echo '<div class="dyk-instruction-step-body"><ol>';
                                        foreach ($val_inst->steps as $val_step) {
                                            echo '<li>' . $val_step . '</li>';
                                        }
                                        echo '</ol></div>';
                                        echo '</div>';
                                    }
                                }
                            }
                        } else {
                            // General or gateway instructions from JSON
                            if(!empty($array) && is_array($array)){
                                $pg_has_instruction = false;
                                foreach ($array as $item) {
                                    if($donate[0]->payment_gateway!='' && $item['pg'] == $donate[0]->payment_gateway && $item['payment'] == $bank_code){
                                        $pg_has_instruction = true;
                                        $inst_title = $item['method'] ?? 'Petunjuk Pembayaran';
                                        if(strtolower($inst_title) == 'instant'){
                                            $inst_title = 'Cara Pembayaran QRIS / E-Wallet';
                                        } elseif(strtolower($inst_title) == 'transfer' || strtolower($inst_title) == 'general'){
                                            $inst_title = 'Cara Pembayaran Transfer Bank';
                                        } else {
                                            $inst_title = ucwords(str_replace('_', ' ', $inst_title));
                                        }
                                        echo '<div class="dyk-instruction-step-item">';
                                        echo '<div class="dyk-instruction-step-header"><span>'.$inst_title.'</span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg></div>';
                                        echo '<div class="dyk-instruction-step-body"><ol>';
                                        if(!empty($item['steps']) && is_array($item['steps'])){
                                            foreach($item['steps'] as $st){
                                                echo '<li>'.strtr($st, $data_field).'</li>';
                                            }
                                        }
                                        echo '</ol></div></div>';
                                    }
                                }
                                if(!$pg_has_instruction){
                                    foreach ($array as $item) {
                                        if($item['pg']=='general' && $item['payment']==$bank_code){
                                            $inst_title = $item['method'] ?? 'Petunjuk Transfer';
                                            if(strtolower($inst_title) == 'instant'){
                                                $inst_title = 'Cara Pembayaran QRIS / E-Wallet';
                                            } elseif(strtolower($inst_title) == 'transfer' || strtolower($inst_title) == 'general'){
                                                $inst_title = 'Cara Pembayaran Transfer Bank';
                                            } else {
                                                $inst_title = ucwords(str_replace('_', ' ', $inst_title));
                                            }

                                            echo '<div class="dyk-instruction-step-item">';
                                            echo '<div class="dyk-instruction-step-header"><span>'.$inst_title.'</span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg></div>';
                                            echo '<div class="dyk-instruction-step-body"><ol>';
                                            if(!empty($item['steps']) && is_array($item['steps'])){
                                                foreach($item['steps'] as $st){
                                                    echo '<li>'.strtr($st, $data_field).'</li>';
                                                }
                                            }
                                            echo '</ol></div></div>';
                                        }
                                    }
                                }
                            }
                        }
                        ?>
                    </div>
                </div>

            </div><!-- end .dyk-main-card -->

        <?php } ?>

            <!-- Footer Powered by -->
            <?php if($powered_by_setting=='1'){ ?>
            <div class="dyk-footer-powered">
                <a href="https://donasiyuk.id" target="_blank">Powered by DonasiYuk</a>
            </div>
            <?php } ?>

        </div><!-- end .dyk-invoice-content -->
    </div><!-- end .dyk-invoice-wrapper -->

    <!-- WhatsApp Flying Widget (if enabled) -->
    <?php if($flying_button_settings=='1' && $page_invoice_button=='1'){ 
        $wa_admin = wa_variants_08_628_2($flying_button_number);
    ?>
    <a href="https://api.whatsapp.com/send?phone=<?php echo $wa_admin; ?>&text=<?php echo urlencode($flying_button_message); ?>" 
       class="whatsapp-float" target="_blank" style="cursor: pointer; position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
       <?php if($flying_button_bubble_text!=''){ ?><div class="chat-bubble"><?php echo $flying_button_bubble_text; ?></div><?php } ?>
       <img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/icons/whatsapp.svg'; ?>" class="whatsapp-icon" alt="WhatsApp CS" />
    </a>
    <?php } ?>

    <!-- Modal Popup Container for Credit Card Iframe if any -->
    <div id="popupOverlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.55);display:none;justify-content:center;align-items:center;z-index:99999">
        <div id="popupBox" style="width:88%;max-width:500px;height:80%;background:#fff;border-radius:12px;position:relative;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,.2)">
            <span id="closePopupBtn" style="position:absolute;top:10px;right:15px;font-size:26px;font-weight:700;color:#333;cursor:pointer;z-index:10">&times;</span>
            <iframe id="popupIframe" src="" style="width:100%;height:100%;border:none;"></iframe>
        </div>
    </div>

	<script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/jquery.min.js';?>"></script>
	<script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/lucide.min.js';?>"></script>
	<script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/lucide-init.js';?>"></script>
	<script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/donasiyuk.min.js';?>"></script>

    <?php if($payment_account=='QRIS - Remitcepat' || ($is_qris && !empty($payment_qrcode) && strpos((string)$payment_qrcode, 'http') !== 0)){ ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        $(function() {
            var textInput = '<?php echo $payment_number; ?>';
            var container = document.getElementById("qr-code-remitcepat");
            if(container && textInput) {
                new QRCode(container, {
                    text: textInput,
                    width: 220,
                    height: 220,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });
            }

            var textDynamic = <?php echo json_encode($payment_qrcode); ?>;
            var containerDynamic = document.getElementById("qr-code-dynamic");
            if(containerDynamic && textDynamic) {
                new QRCode(containerDynamic, {
                    text: textDynamic,
                    width: 220,
                    height: 220,
                    colorDark : "#000000",
                    colorLight : "#ffffff",
                    correctLevel : QRCode.CorrectLevel.H
                });
            }
        });
    </script>
    <?php } ?>

	<script>
    var d_status = "<?php echo $donate[0]->status; ?>";
    var d_id = <?php echo $donate[0]->id; ?>;
    var ajax_url = "<?php echo $home_url; ?>/wp-admin/admin-ajax.php";

    // 1. Live Countdown Timer
    var targetTimestamp = <?php echo $expired_timestamp; ?>;
    function startCountdown() {
        function updateTimer() {
            var now = Math.floor(Date.now() / 1000);
            var diff = targetTimestamp - now;
            if (diff <= 0) {
                $('#countdown-timer').text('00:00:00');
                return;
            }
            var hours = Math.floor(diff / 3600);
            var minutes = Math.floor((diff % 3600) / 60);
            var seconds = diff % 60;
            
            var strH = hours < 10 ? '0' + hours : hours;
            var strM = minutes < 10 ? '0' + minutes : minutes;
            var strS = seconds < 10 ? '0' + seconds : seconds;
            
            $('#countdown-timer').text(strH + ':' + strM + ':' + strS);
        }
        updateTimer();
        setInterval(updateTimer, 1000);
    }
    startCountdown();

    // 2. Total Donation Accordion
    $(document).on('click', '#toggle-total-detail', function() {
        $('#dyk-total-breakdown').slideToggle(200);
        $('.dyk-total-chevron').toggleClass('open');
    });

    // 3. Instructions Accordion
    $(document).on('click', '#toggle-instructions', function() {
        $('#dyk-instructions-content').slideToggle(200);
        $('.dyk-instructions-arrow').toggleClass('open');
    });

    $(document).on('click', '.dyk-instruction-step-header', function() {
        var $body = $(this).siblings('.dyk-instruction-step-body');
        var isActive = $(this).hasClass('active');
        
        $('.dyk-instruction-step-header').removeClass('active');
        $('.dyk-instruction-step-body').slideUp(200);
        
        if (!isActive) {
            $(this).addClass('active');
            $body.slideDown(200);
        }
    });

    // 4. Copy to Clipboard
    $('.btn-copy-action').on('click', function(e) {
        e.preventDefault();
        var text = $(this).attr('data-salin');
        if (!text) return;
        
        copyToClipboard(text);
        
        var $btn = $(this);
        var oldText = $btn.text();
        $btn.text('Tersalin!').css({'background':'#0099ff','color':'#fff'});
        setTimeout(function() {
            $btn.text(oldText).css({'background':'','color':''});
        }, 2000);

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top',
                icon: 'success',
                title: 'Berhasil disalin ke clipboard!',
                showConfirmButton: false,
                timer: 2000
            });
        }
    });

    function copyToClipboard(string) {
        var textarea = document.createElement("textarea");
        textarea.value = string;
        textarea.setAttribute("readonly", "");
        textarea.style.position = "fixed";
        textarea.style.opacity = "0";
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        document.execCommand("copy");
        document.body.removeChild(textarea);
    }

    // 5. Check Payment Status (Manual & Background)
    if(d_status != '1'){
        setTimeout(auto_get_status, 5000);
    }

    function auto_get_status() {
        var data_nya = [d_status, d_id];
        $.post(ajax_url, { "action": "dykfunction_get_status", "datanya": data_nya }, function(response) {
            if(response == 'success'){
                location.reload();
            } else {
                setTimeout(auto_get_status, 4000);
            }
        });
    }

    $('#btn-manual-check').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $text = $('#btn-check-text');
        
        $btn.attr('disabled', true).css('opacity', '0.7');
        $text.text('Mengecek status...');

        var data_nya = [d_status, d_id];
        $.post(ajax_url, { "action": "dykfunction_get_status", "datanya": data_nya }, function(response) {
            $btn.attr('disabled', false).css('opacity', '1');
            $text.text('Cek status pembayaran');
            
            if(response == 'success'){
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pembayaran Diterima!',
                        text: 'Terima kasih, donasi Anda telah kami terima.',
                        confirmButtonColor: '#10b981'
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Belum Terdeteksi',
                        text: 'Pembayaran belum kami terima. Silakan selesaikan pembayaran dan cek kembali beberapa saat lagi.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0099ff'
                    });
                } else {
                    alert('Pembayaran belum kami terima. Silakan selesaikan pembayaran.');
                }
            }
        });
    });

    // 6. Proof of Payment Upload Handler
    $('#btn-select-proof').on('click', function() {
        $('#dyk-file-input').click();
    });

    $('#dyk-file-input').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(evt) {
                $('#dyk-proof-preview').attr('src', evt.target.result);
                $('#dyk-proof-preview-wrap').slideDown();
                $('#btn-select-proof').text('Ganti Foto Bukti Transfer');
            };
            reader.readAsDataURL(file);
        }
    });

    $('#btn-submit-proof').on('click', function(e) {
        e.preventDefault();
        var fileInput = $('#dyk-file-input')[0];
        if(!fileInput.files || !fileInput.files[0]) return;

        var formData = new FormData();
        formData.append('updoc', fileInput.files[0]);
        formData.append('action', "donasiyuk_upload_confirmation");

        var $btn = $(this);
        $btn.attr('disabled', true).text('Mengirim...');

        $.ajax({
            url: ajax_url,
            type: "POST",
            data: formData,
            cache: false,
            processData: false,
            contentType: false,
            success: function(data) {
                if(data != 'failed'){
                    var data_nya = [data, "<?php echo $invoice_id;?>"];
                    $.post(ajax_url, { "action": "dykfunction_update_confirmation", "datanya": data_nya }, function(res) {
                        if(res == 'success'){
                            Swal.fire('Berhasil!', 'Bukti transfer berhasil dikirim.', 'success');
                            $('#upload-proof-box').html('<div style="color:#10b981;font-weight:700;padding:10px;">✓ Bukti transfer sudah kami terima.</div>');
                        } else {
                            Swal.fire('Gagal', 'Gagal memperbarui data konfirmasi.', 'error');
                            $btn.attr('disabled', false).text('Kirim Bukti Transfer');
                        }
                    });
                } else {
                    Swal.fire('Gagal', 'File gagal diunggah.', 'error');
                    $btn.attr('disabled', false).text('Kirim Bukti Transfer');
                }
            }
        });
    });

    // 7. Credit Card Modal or Deeplink
    <?php if($bank_code=='cc' && $donate[0]->status!='1' && !empty($donate[0]->deeplink_url)){ ?>
    window.onload = function () {
        document.getElementById("popupOverlay").style.display = "flex";
        document.getElementById("popupIframe").src = "<?php echo $donate[0]->deeplink_url; ?>";
    };
    document.getElementById("closePopupBtn").onclick = function () {
        document.getElementById("popupOverlay").style.display = "none";
        document.getElementById("popupIframe").src = "";
    };
    <?php } ?>

	</script>

	<?php if($gtm_id!=''){ ?>
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $gtm_id;?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php } ?>
    
</body>
</html>