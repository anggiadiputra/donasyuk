<?php
	
	set_time_donasiaja();

	global $wpdb;
	global $wp;
    $table_name = $wpdb->prefix . "dyk_campaign";
    $table_name2 = $wpdb->prefix . "dyk_donate";
    $table_name3 = $wpdb->prefix . "dyk_users";
    $table_name4 = $wpdb->prefix . "dyk_love";
    $table_name5 = $wpdb->prefix . "dyk_settings";
    $table_name6 = $wpdb->prefix . "dyk_campaign_update";
    $table_name7 = $wpdb->prefix . "dyk_aff_code";
    $table_name8 = $wpdb->prefix . "dyk_aff_submit";
    $table_name9 = $wpdb->prefix . "users";
    $table_name10 = $wpdb->prefix . "dyk_aff_click";

    donasiyuk_global_vars();
    $plugin_version = $GLOBALS['donasiyuk_vars']['plugin_version'];

    // Check IP User
    check_blocked_ip();

    // Currency
    $query_currency = $wpdb->get_results('SELECT data from '.$table_name5.' where type="currency"  ORDER BY id ASC');
    $currency = $query_currency[0]->data;
    $lang = get_data_lang($currency);
    $langArray = require_once(ROOTDIR_DYK . 'library/locale/'.$lang.'.php');

    $show_currency = donasiyuk_currency($currency);
    
    // Settings
    $query_settings = $wpdb->get_results('SELECT data from '.$table_name5.' where type="label_tab" or type="max_love" or type="app_name" or type="login_setting" or type="page_login" or type="anonim_text" or type="page_donate" or type="theme_color" or type="form_text" or type="powered_by_setting" or type="fb_pixel" or type="fb_event" or type="gtm_id" or type="limitted_donation_button" or type="tiktok_pixel" or type="fundraiser_on" or type="fundraiser_text" or type="fundraiser_button" or type="readmore_description" or type="metapixel_only" or type="metapixel_convertion" or type="metapixel_convertion_data" or type="user_bywa" or type="baznas_referral_code" or type="flying_button_settings" or type="flying_button_bubble_text" or type="flying_button_message" or type="flying_button_number" or type="flying_button_page_settings" or type="data_fundraiser_settings" ORDER BY id ASC');
    $label_tab 	 = $query_settings[0]->data;
    $max_love 	 = $query_settings[1]->data;
    $app_name	 = $query_settings[2]->data;
    $login_setting 	= $query_settings[3]->data;
    $page_login 	= $query_settings[4]->data;
    $anonim_text = $query_settings[5]->data;
    $page_donate = $query_settings[6]->data;
    $general_theme_color = json_decode($query_settings[7]->data, true);
    $form_text 	 = json_decode($query_settings[8]->data, true);
    $powered_by_setting = $query_settings[9]->data;
    $fb_pixel 	 = $query_settings[10]->data;
    $fb_event  	 = json_decode($query_settings[11]->data, true);
    $event_1   	 = $fb_event['event'][0];
    $event_2   	 = $fb_event['event'][1];
    $event_3   	 = $fb_event['event'][2];
    if(isset($fb_event['event'][3])){
        $event_4  = $fb_event['event'][3];
    }else{
        $event_4  = '';
    }
    $gtm_id 	 = $query_settings[12]->data;
    $limitted_donation_button = $query_settings[13]->data;
    $tiktok_pixel = $query_settings[14]->data;
    $fundraiser_on 		= $query_settings[15]->data;
    $fundraiser_text 	= $query_settings[16]->data;
    $fundraiser_button 	= $query_settings[17]->data;
    $readmore_description = $query_settings[18]->data;
    $metapixel_only 			= $query_settings[19]->data;
    $metapixel_convertion 		= $query_settings[20]->data;
    $metapixel_convertion_data 	= $query_settings[21]->data;
    // $user_bywa 					= $query_settings[22]->data;
    $baznas_referral_code 		= $query_settings[23]->data;
    $flying_button_settings        = $query_settings[24]->data;
    $flying_button_bubble_text     = $query_settings[25]->data;
    $flying_button_message         = $query_settings[26]->data;
    $flying_button_number          = $query_settings[27]->data;
	$flying_button_page_settings   = $query_settings[28]->data;
	$data_fundraiser_settings      = $query_settings[29]->data;

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
        // Fallback default value kalau JSON tidak valid / kosong
        $page_campaign_button = null;
        $page_form_button     = null;
        $page_invoice_button  = null;
    }

    // for check update -> user_bywa
    if(isset($query_settings[22]->data)) { 
       // aman, data ada artinya sudah terinstall update terbaru
    } else { 
        // start install
        dyk_options_install();
        dyk_options_install_data();
    } 

    // meta pixel only - general
    if($metapixel_only=='1'){
    	$fb_pixel  = $fb_pixel;
    }

    if($metapixel_only==null){
    	$fb_pixel  = $fb_pixel;
    }

    // meta pixel convertion - general
    if($metapixel_convertion=='1'){
    	if($metapixel_convertion_data!=''){
	        $metapixel_convertion_data = json_decode($metapixel_convertion_data, true);
	        $jumlah_pixel = $metapixel_convertion_data['jumlah'];
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

    // set the color
    $theme_color 		= !empty($general_theme_color['color'][0]) ? $general_theme_color['color'][0] : '#10a8e5';
	$progressbar_color  = !empty($general_theme_color['color'][1]) ? $general_theme_color['color'][1] : '#10a8e5';
	$button_color 		= !empty($general_theme_color['color'][2]) ? $general_theme_color['color'][2] : '#10a8e5';

	if(empty($button_color) || $button_color=='#7680ff' || $button_color=='#7680FF'){
		$button_color = '#10a8e5';
	}
	if(empty($progressbar_color) || $progressbar_color=='#009F61' || $progressbar_color=='#24CC63'){
		$progressbar_color = '#10a8e5';
	}

	$text1 = $form_text['text'][0];
	$text2 = $form_text['text'][1];
	$text3 = $form_text['text'][2];
	$text4 = $form_text['text'][3];

	$slug = $donasi_id;
	$check = $wpdb->get_results('SELECT id from '.$table_name.' where slug="'.$slug.'"');
	if($check==null){
		$check2 = $wpdb->get_results('SELECT id, slug from '.$table_name.' where campaign_id="'.$slug.'"');
		$slug = $check2[0]->slug;
		if($check2==null){
			wp_redirect( get_site_url() );
			exit;
		}
	}

	// **********************
	// GET DATA CAMPAIGN
	// **********************
	$row = $wpdb->get_results('SELECT * from '.$table_name.' where slug="'.$slug.'"')[0];

    if($row->pixel_status=='1' && !empty($row->fb_event)){
	    // fb event
	    $fb_event  = json_decode($row->fb_event, true);
	    $event_1   = $fb_event['event'][0];
	    $event_2   = $fb_event['event'][1];
	    $event_3   = $fb_event['event'][2];
	    if(isset($fb_event['event'][3])){
	        $event_4  = $fb_event['event'][3];
	    }else{
	        $event_4  = '';
	    }
	}

    // meta pixel only
    if($row->pixel_status=='1' and $row->metapixel_only=='1'){
    	$fb_pixel  = $row->fb_pixel;
    }
    
    // kondisi untuk yang baru update ke 1.8 agar fb pixel tetap ke firing
    if($row->pixel_status=='1' and $row->metapixel_only==null){
        $fb_pixel  = $row->fb_pixel;
    }

    // meta pixel and convertion
    if($row->pixel_status=='1' and $row->metapixel_convertion=='1'){

	    if($row->metapixel_convertion_data!=''){
	        $metapixel_convertion_data = json_decode($row->metapixel_convertion_data, true);
	        $jumlah_pixel = $metapixel_convertion_data['jumlah'];
	    }else{
	        $jumlah_pixel = 0;
	    }
	    
	    $fb_pixel_convertion = '';
	    if($jumlah_pixel>=1){
	        $count_pixel = 1;
	        foreach ($metapixel_convertion_data['data'] as $key => $value) {
	        	// echo $value[0].'<br>';
	        	// echo $value[1].'<br>';
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

    if($row->gtm_status=='1'){
    	$gtm_id  = $row->gtm_id;
    }
    if($row->tiktok_status=='1'){
    	$tiktok_pixel  = $row->tiktok_pixel;
    }

    $general_status = $row->general_status;
    $allocation_title = $row->allocation_title;
    $allocation_others_title = $row->allocation_others_title;
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

    // button zakat
    if($row->form_type=='4' || $row->form_type=='7'){
        $text1 = 'Zakat Sekarang';
    }else{
        $text1 = $allocation_title.' Sekarang';
    }
    if (strpos(strtolower($allocation_title), 'wakaf') !== false ) {
        $text1 = 'Wakaf Sekarang';
    }

    if($row->form_status=='1' && !empty($row->form_text)){
        $form_text   = json_decode($row->form_text, true);
        $text1 = $form_text['text'][0];
        $text2 = $form_text['text'][1];
        $text3 = $form_text['text'][2];
        $text4 = $form_text['text'][3];
    }

    if($general_status=='1'){
	    $donatur_name = $row->donatur_name;
	    $donatur_others_name = $row->donatur_others_name;
	    if($donatur_name==1 || $donatur_name==0){
	        if($currency=='MYR'){
				$donatur_title = "Penderma";
			}else{
				$donatur_title = "Donatur";
			}
	    }elseif($donatur_name==2){
	        $donatur_title = "Muzakki";
	    }elseif($donatur_name==3){
	        $donatur_title = "Wakif";
	    }else{
	        $donatur_title = $donatur_others_name;
	        if($donatur_title==''){
	        	$donatur_title = "Donatur";
	        }
	    }
	}else{
		if($currency=='MYR'){
			$donatur_title = "Penderma";
		}else{
			$donatur_title = "Donatur";
		}
	}

	if($general_status=='1'){
		if($row->home_icon_url!=''){
			$home_urlnya = $row->home_icon_url;
		}else{
			$home_urlnya = get_site_url();
		}
	}else{
		$home_urlnya = get_site_url();
	}

	// GET HOME ICON
	$home_icon = '1';
    if($general_status=='1'){
    	if($row->icon_setting!=''){
	    	$icon_setting = json_decode($row->icon_setting, true);
	        $home_icon = $icon_setting['home_icon'];
    	}
    }


	// check campaign is published or not
	if($link_code=='campaign'){
		if($row->publish_status=='1' || $row->publish_status=='4'){
		}else{
			wp_redirect( get_site_url() );
			exit;
		}
	}


	// custom whatsapp flying button
	$flying_button_status = $row->flying_button_status;
	if($flying_button_status=='1'){
		$flying_button_settings        = $row->flying_button_settings;
	    $flying_button_bubble_text     = $row->flying_button_bubble_text;
	    $flying_button_message         = $row->flying_button_message;
	    $flying_button_number          = $row->flying_button_number;
		$flying_button_page_settings   = $row->flying_button_page_settings;

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
	        // Fallback default value kalau JSON tidak valid / kosong
	        $page_campaign_button = null;
	        $page_form_button     = null;
	        $page_invoice_button  = null;
	    }
	}
	

	// GET CAMPAIGN UPDATE
	$campaign_update = $wpdb->get_results("SELECT * FROM $table_name6 where campaign_id='$row->campaign_id' ORDER BY id DESC");

	// GET TOTAL DONASI
	$total_donasi = $wpdb->get_results("SELECT SUM(nominal) as total, COUNT(id) as jumlah FROM $table_name2 where campaign_id='$row->campaign_id' and status='1' ")[0];

	// target : 0 = unlimitted target
	if($row->target==0){
		$persen = 100;
		$persen_width = 100;
	}else{
		$persen = ($total_donasi->total/$row->target)*100;
		$persen_width = ($total_donasi->total/$row->target)*100;
		if($persen_width>100){
			$persen_width = 100;
		}
	}

	// GET INFORMATION

	// print_r($row->information);
	
	$information = str_replace(': center', ':center', $row->information);
	$information = str_replace(': justify', ':justify', $information);
	$information = str_replace(': left', ':left', $information);
	$information = str_replace(': #', ':#', $information);
	$information = str_replace("'", "&#39;", $information); // petik 1
    // $information = str_replace('"', "&#34;", $information); // petik 2
    $information = str_replace('../wp-content', get_site_url().'/wp-content', $information);
    // $information = str_replace('"', '', $information);

    // $information = $row->information;
    // print_r($information);
    
	$information_for_head = substr($information, 0, 450);

	// GER ORANG YANG DONASI TERBARU
	$donasi = $wpdb->get_results("SELECT * FROM $table_name2 where campaign_id='$row->campaign_id' and status='1' ORDER BY id DESC limit 0,5 ");

	// GER ORANG YANG DONASI TERBESAR
	$donasi2 = $wpdb->get_results("SELECT * FROM $table_name2 where campaign_id='$row->campaign_id' and status='1' ORDER BY nominal DESC limit 0,5 ");

	// GER ORANG YANG DONASI ADA KOMENNYA
	$donasi_comment = $wpdb->get_results("SELECT * FROM $table_name2 where campaign_id='$row->campaign_id' and status='1' and comment!='' ORDER BY id DESC limit 0,5 ");

	$data_comment = $wpdb->get_results("SELECT COUNT(id) as jumlah FROM $table_name2 where campaign_id='$row->campaign_id' and status='1' and comment!=''  ")[0];

	// GET DATA USER
	$user_info = get_userdata($row->user_id);
  	$fullname = $user_info->first_name.' '.$user_info->last_name;
  	$fullname = wp_strip_all_tags($fullname);

  	// GET CURRENT URL
  	$home_url = get_site_url();
  	if($link_code=='campaign'){
		$current_url = get_site_url().'/campaign/'.$slug;
	}else{
		$current_url = get_site_url().'/preview/'.$slug;
	}

	// GET PROFILE PICTURE
	$profile = $wpdb->get_results('SELECT user_pp_img as photo, user_type, user_verification, user_randid  from '.$table_name3.' where user_id="'.$row->user_id.'"');
	if(isset($profile[0])){
		if($profile==null){
			$profile_photo = plugin_dir_url( __FILE__ ) . "assets/images/pp.svg";
		}else{
			$profile_photo = $profile[0]->photo;
			if($profile_photo==null){
				$profile_photo = plugin_dir_url( __FILE__ ) . "assets/images/pp.svg";
			}
		}
	}else{
		$profile_photo = plugin_dir_url( __FILE__ ) . "assets/images/pp.svg";
	}

	// print_r($profile);

	// Waktu Berakhir
    $date_now = date('Y-m-d');
    $datetime1 = new DateTime($date_now);
    // $datetime2 = new DateTime($row->end_date);
    if (!empty($row->end_date)) {
        $datetime2 = new DateTime($row->end_date);
    } else {
        // Handle null end_date: set $datetime2 to $datetime1 or a default future date
        $datetime2 = new DateTime('9999-12-31'); // You can use any default future date
    }
    $hasil = $datetime1->diff($datetime2);
    
    $year = $hasil->y;
    $month = $hasil->m;
    $day = $hasil->d;

    // Date
    $date_end = false;
    if($year!=0){
        if($day>7){
    		$sisa_waktu = $year.'&nbsp;tahun,&nbsp;' .($month+1).'&nbsp;bulan&nbsp;lagi';
    	}else{
    		$sisa_waktu = $year.'&nbsp;tahun,&nbsp;' .$month.'&nbsp;bulan&nbsp;lagi';
    	}
    }else{
        if($month!=0){
            $sisa_waktu = $month.'&nbsp;bulan,&nbsp;' .$day.'&nbsp;hari&nbsp;lagi';
        }else{
            if($day==0 && $hasil->days==0){
                $sisa_waktu = 'hari&nbsp;ini';
            }else{
                if($hasil->invert==true){
                    $sisa_waktu = '<span style="color:#ff6b24;font-style:italic;">sudah&nbsp;berakhir</span>';
                    $date_end = true;
                }else{
                    $sisa_waktu = $day.'&nbsp;hari&nbsp;lagi';
                }
                
            }
        }
    }

    if($row->end_date==null){
    	$sisa_waktu = '∞&nbsp;hari&nbsp;lagi';
    }

    if($hasil->invert==true){
    	$sisa_waktu = '<span style="color:#ff6b24;font-style:italic;">sudah&nbsp;berakhir</span>';
    }

    if($row->publish_status=='3'){
    	$sisa_waktu = '<span style="color:#ff6b24;font-style:italic;">Archived</span>';
    }

    // Settings Socialproof
    $query_settings2 = $wpdb->get_results('SELECT data from '.$table_name5.' where type="anonim_text" or type="socialproof_text" or type="socialproof_settings" ORDER BY id ASC');
    $anonim_text    	  = $query_settings2[0]->data;
    $socialproof_text     = $query_settings2[1]->data;
    $socialproof_settings = $query_settings2[2]->data;

    $socialproof_setting  = json_decode($socialproof_settings, true);
	$popup_style ='rounded';
	$delay = 8;
	$data_load = 10;
	$time_set = 1;
    if($socialproof_setting!=''){
		$popup_style    = $socialproof_setting['settings'][0];
		$position       = $socialproof_setting['settings'][1];
		$time_set       = $socialproof_setting['settings'][2];
		$delay          = $socialproof_setting['settings'][3];
		$data_load      = $socialproof_setting['settings'][4];
	}
    

    // close
    $close = 'false';

    // popup_style
    if($popup_style=='rounded'){
        $set_style = ' s-rounded';
    }elseif($popup_style=='flying_boxed'){
        $set_style = ' s-flying';
    }elseif($popup_style=='flying_rounded'){
        $set_style = ' s-rounded s-flying';
    }else{
        $set_style = '';
    }

    // delay
    $delay = $delay*1000;

    // data_load
    $total = $data_load;

    // time
    $time = $time_set;

    // title
    $title = $socialproof_text;

    // position
    $p_gravity = 'top';
    $p_position = 'left';
    if($socialproof_setting!=''){
	    $position_data = explode('_', $position);
		$p_gravity  = $position_data[0];
		$p_position = $position_data[1];
	}

	// set custom campaign socialproof
	if($row->socialproof_text!=''){
		$title = $row->socialproof_text;
	}

	if($row->socialproof_position!=''){
		$position_data_new = explode('_', $row->socialproof_position);
		$p_gravity  = $position_data_new[0];
		$p_position = $position_data_new[1];
	}

	// campaign
	$data_donasi = $wpdb->get_results("SELECT a.id, a.campaign_id, a.user_id, a.invoice_id, a.name, a.anonim, a.created_at, a.nominal as total, b.title, c.user_pp_img FROM $table_name2 a
	left JOIN $table_name b ON b.campaign_id = a.campaign_id
	left JOIN $table_name3 c ON c.user_id = a.user_id
	where a.status='1' and a.campaign_id='$row->campaign_id' ORDER BY id DESC LIMIT 0,$total ");

	$data_donasinya = '';
	foreach ($data_donasi as $value) {
		
		$donatur_name = $value->name;
		if($value->anonim=='1'){
			$donatur_name = $anonim_text;
		}
		$donatur_name = wp_strip_all_tags($donatur_name);

		// if(strpos($title, '{campaign_title}') !== false) {
		//     $title = str_replace("{campaign_title}", $value->title, $title);
		// }

		// if(strpos($title, '{total}') !== false) {
		//     $title = str_replace("{total}", $show_currency.number_format_currency($value->total), $title);
		// }

		$data_field = array();
	    $data_field[ '{campaign_title}' ] = $value->title;
	    $data_field[ '{total}' ] = $show_currency.number_format_currency($value->total);
	   
		$titlenya = strtr($title, $data_field);

		$pp = '';
		if($value->user_pp_img!=''){
			$pp = $value->user_pp_img;
		}

		$the_time = donasiyuk_readtime($value->created_at);

		$donatur_name = str_replace("'",'',$donatur_name);
		$donatur_name = str_replace('"','',$donatur_name);

		$title_campaign = str_replace("'",'',$titlenya);
		$title_campaign = str_replace('"','',$title_campaign);
		
		$data_donasinya .= '{"content": ["'.$donatur_name.'", "'.$the_time.'", "'.$title_campaign.'", "'.$pp.'", "'.$value->campaign_id.'"]},';
	}

	$id_login = wp_get_current_user()->ID;

	$aff_code = '';
	if($id_login!=null){
		$rows_aff = $wpdb->get_results("SELECT aff_code from $table_name7 where campaign_id='$row->campaign_id' and user_id='$id_login' ORDER BY id DESC");
		if($rows_aff!=null){
			$aff_code = $rows_aff[0]->aff_code;
		}
	}


	$get_fundraiser = $wpdb->get_results("SELECT a.campaign_id, c.user_id as fundraiser_id, count(a.id) as jumlah_donatur, sum(b.nominal) as total
	FROM $table_name8 a 
	LEFT JOIN $table_name7 c on c.id = a.affcode_id 
	LEFT JOIN $table_name2 b on b.id = a.donate_id 
	where a.campaign_id='$row->campaign_id' and b.status = '1'
	GROUP BY fundraiser_id ORDER BY total DESC limit 0,5");

	$all_fundraiser = $wpdb->get_results("SELECT a.campaign_id, c.user_id as fundraiser_id, count(a.id) as jumlah_donatur, sum(b.nominal) as total
	FROM $table_name8 a 
	LEFT JOIN $table_name7 c on c.id = a.affcode_id 
	LEFT JOIN $table_name2 b on b.id = a.donate_id 
	where a.campaign_id='$row->campaign_id' and b.status = '1'
	GROUP BY fundraiser_id ORDER BY total DESC");

	$hex = $button_color;
	list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
	$colornya = 'rgba('.$r.','.$g.','.$b.', 0.15)';
	$colornya_soft = 'rgba('.$r.','.$g.','.$b.', 0.04)';
	$color_hovernya = 'rgba('.$r.','.$g.','.$b.', 0.25)';



	// affcode
	$aff_ip = donasiyuk_getIP();
    $aff_os = donasiyuk_getOS();
    $aff_browser = donasiyuk_getBrowser();

    if (strpos($affcode, '&') !== false ) {
		$get_affcode = explode('&',$affcode);
		$get_affcode = $get_affcode[0];
	}else{
		$get_affcode = $affcode;
	}

	if (strpos($get_affcode, 'ref=') !== false ) {
		$data_affcode = explode('ref=',$get_affcode);
		$data_affcode = $data_affcode[1];
	}else{
		$data_affcode = '';
	}

	$link_ref_aff = '';
	$affcode_id = '';
    if($data_affcode!=''){
    	// get aff_code
    	$check_affcode = $wpdb->get_results('SELECT * from '.$table_name7.' where aff_code="'.$data_affcode.'" ');
    	if($check_affcode!=null){
    		$affcode_id = $check_affcode[0]->id;
    		// $link_ref_aff = "?ref=$data_affcode";
    		$link_ref_aff = "?".$affcode;
    		
    		list ( $y, $m, $d ) = explode('.', date('Y.m.d'));
    		$today_start = mktime(0, 0, 0, $m, $d, $y);
			$today_end   = mktime(23, 59, 59, $m, $d, $y);
    		$check_log = $wpdb->get_results("SELECT * from $table_name10 where campaign_id='$row->campaign_id' and affcode_id='$affcode_id' and ip='$aff_ip' and os='$aff_os' and browser='$aff_browser' and created_at >= CURDATE()");
    		if($check_log==null){
    			// insert log
    			$wpdb->insert( $table_name10,
		            array(
		                'campaign_id'   => $row->campaign_id,
		                'affcode_id'    => $affcode_id,
		                'ip'    		=> $aff_ip,
		                'os'    		=> $aff_os,
		                'browser'    	=> $aff_browser,
		                'created_at'    => date("Y-m-d H:i:s")),
		            array('%s', '%s')       
		        );
    		}
    	}else{
    		// link tanpa dengan aff code tapi aff code gak valid, jadi aff code tidak diteruskan
    		$data_link = explode($data_affcode.'&',$affcode);
			$data_link_selanjutnya = $data_link[1] ?? '';

    		$link_ref_aff = "?".$data_link_selanjutnya;
    	}
    }else{
    	// link tanpa aff code
    	$link_ref_aff = "?".$affcode;
    }


    // {
    	// "link1":["0","zakat sekarang"],
    	// "link2":["1","Hubungi CS","6281320139386","text"],
    	// "link3":["0","Hubungi Admin","link"]
	// }

    $external_link = '';
    $option_baznasjabar_link = 0;
    $option_whatsapp_link = 0;
    $option_custom_link = 0;
    if (!empty($row->external_link_button)) {

        $external_link_button = $row->external_link_button;

        $external_link_data = !empty($row->external_link_data) ? json_decode($row->external_link_data, true) : [];

        if (is_array($external_link_data)) {

            foreach($external_link_data as $key => $value){

                if($key=='link1'){
                    $option_baznasjabar_link = $value[0];
                    $baznasjabar_button = $value[1];
                    
                }
                if($key=='link2'){
                    $option_whatsapp_link = $value[0];
                    $whatsapp_button = $value[1];
                    $whatsapp_number = $value[2];
                    $whatsapp_text = $value[3];
                }
                if($key=='link3'){
                    $option_custom_link = $value[0];
                    $custom_button = $value[1];
                    $custom_link = $value[2];
                }
            }

        }else{
            $option_baznasjabar_link = 1;
            $option_whatsapp_link = 0;
            $option_custom_link = 0;
        }


        // Set Button and Link
        if($option_baznasjabar_link=='1'){
        	$text1 = $baznasjabar_button;
        	$external_link = 'https://baznasjabar.org/zakat?ref='.$baznas_referral_code;
        }
        if($option_whatsapp_link=='1'){
        	$text1 = $whatsapp_button;

        	$phone = djaPhoneFormat($whatsapp_number,$currency);

        	$message = dyk_whatsapp_format($whatsapp_text);
			$message = str_replace("\\", '', $message);
			$message = str_replace("&#39;", "'", $message);
			$message = strip_tags($message ?? '');

        	$external_link = 'https://api.whatsapp.com/send?phone='.$phone.'&text='.$message;
        }
        if($option_custom_link=='1'){
        	$text1 = $custom_button;
        	$external_link = $custom_link;
        }

    }

    

?>
<!-- Powered by DonasiYuk.id -->
<!DOCTYPE html>
<html lang="en-US">
<head>
	<title><?php echo get_langArray('f_campaign_title1');?> - <?php echo $row->title.' | '.$app_name; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=0">
	<meta name="application-name" content="<?php echo $current_url; ?>"/>
	<meta name="title" content="<?php echo $row->title; ?>">
	<meta name="description" content="<?php echo strip_tags($information_for_head ?? ''); ?>">
	<meta property="og:url" content="<?php echo $current_url; ?>" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="<?php echo $row->title; ?>" />
	<meta property="og:description" content="<?php echo strip_tags($information_for_head ?? ''); ?>" />
<?php if($row->image_url!=null){?>
	<meta property="og:image" content="<?php echo $row->image_url; ?>" />
<?php }else{?>
	<meta property="og:image" content="<?php echo plugin_dir_url( __FILE__ ).'admin/images/cover_donasiyuk.jpg'; ?>" />
<?php } ?>
	<meta property="twitter:card" content="summary_large_image">
	<meta property="twitter:url" content="<?php echo $current_url; ?>">
	<meta property="twitter:title" content="<?php echo $row->title; ?>">
	<meta property="twitter:description" content="<?php echo strip_tags($information_for_head ?? ''); ?>">
	<?php if($row->image_url!=null){?>
	<meta property="twitter:image" content="<?php echo $row->image_url; ?>" />
<?php }else{?>
	<meta property="twitter:image" content="<?php echo plugin_dir_url( __FILE__ ).'admin/images/cover_donasiyuk.jpg'; ?>" />
<?php } ?>
	<?php dyk_set_favicon(); ?>
	<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ) . 'assets/css/donasiyuk.css?ver='.$GLOBALS['donasiyuk_vars']['plugin_version'].'';?>">
	<style type="text/css">
		/* Typography matching Invoice Page */
		*, *::before, *::after,
		html, body, input, button, select, textarea,
		h1, h2, h3, h4, h5, h6, p, span, a, div,
		.section-box, .container--tabs, .donation_name, .donation_total, .donation_comment, .dyk-nav-list-card, .dyk-penggalang-box {
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
			-webkit-font-smoothing: antialiased !important;
			-moz-osx-font-smoothing: grayscale !important;
		}

		body {
			color: #1e293b !important;
		}

		a:active,a:focus,a:visited{box-shadow:none!important;outline:none;box-shadow:0 4px 15px 0 rgba(0,0,0,.1)}.loc_name{margin-top: -20px;padding-left: 25px;font-size: 13px;color: #a3aab0;}.d_map:hover .loc_name{color:#2196F3!important;transition:all 0.25s ease-in-out}.fancy-button{margin:auto;position:relative}.frills,.frills:after,.frills:before{position:absolute;background:#eb1f48;border-radius:4px;height:4px}.frills:after,.frills:before{content:"";display:block}.frills:before{bottom:15px}.frills:after{top:15px}.left-frills{right:180px;top:0}.active .left-frills{-webkit-animation:move-left 0.38s ease-out,width-to-zero 0.38s ease-out;animation:move-left 0.38s ease-out,width-to-zero 0.38s ease-out}.left-frills:before,.left-frills:after{left:15px}.active .left-frills:before{-webkit-animation:width-to-zero 0.38s ease-out,move-up 0.38s ease-out;animation:width-to-zero 0.38s ease-out,move-up 0.38s ease-out}.active .left-frills:after{-webkit-animation:width-to-zero 0.38s ease-out,move-down 0.38s ease-out;animation:width-to-zero 0.38s ease-out,move-down 0.38s ease-out}.right-frills{left:40px;top:0}.active .right-frills{-webkit-animation:move-right 0.38s ease-out,width-to-zero 0.38s ease-out;animation:move-right 0.38s ease-out,width-to-zero 0.38s ease-out}.right-frills:before,.right-frills:after{right:15px}.active .right-frills:before{-webkit-animation:width-to-zero 0.38s ease-out,move-up 0.38s ease-out;animation:width-to-zero 0.38s ease-out,move-up 0.38s ease-out}.active .right-frills:after{-webkit-animation:width-to-zero 0.38s ease-out,move-down 0.38s ease-out;animation:width-to-zero 0.38s ease-out,move-down 0.38s ease-out}.left-frills:before,.right-frills:after{transform:rotate(34deg)}.left-frills:after,.right-frills:before{transform:rotate(-34deg)}.total_love span{color:#F43756}.plus1{font-size:11px;margin-left:5px;position:absolute;top:0;color:#F43756;display:none}.plus1.show{display:inline}@-webkit-keyframes move-left{0%{transform:none}65%{transform:translateX(-30px)}100%{transform:translateX(-30px)}}@keyframes move-left{0%{transform:none}65%{transform:translateX(-80px)}100%{transform:translateX(-80px)}}@-webkit-keyframes move-right{0%{transform:none}65%{transform:translateX(80px)}100%{transform:translateX(80px)}}@keyframes move-right{0%{transform:none}65%{transform:translateX(80px)}100%{transform:translateX(80px)}}@-webkit-keyframes width-to-zero{0%{width:18px}100%{width:8px}}@keyframes width-to-zero{0%{width:18px}100%{width:8px}}@-webkit-keyframes move-up{100%{bottom:69.75px}}@keyframes move-up{100%{bottom:69.75px}}@-webkit-keyframes move-down{100%{top:69.75px}}@keyframes move-down{100%{top:69.75px}}
		.video-container { position: relative; padding-bottom: 56.25%; padding-top: 30px; height: 0; overflow: hidden; }
		.video-container iframe, .video-container object, .video-container embed { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
		.donation_button_fundraiser{display:inline-block;border:0 none;font-weight:700;line-height:normal;text-align:center;vertical-align:middle;cursor:pointer;transition:all .35s ease 0s;text-decoration:none;border-radius:4px;width:100%;padding:12px 45px;font-size:16px;background-color:#dc3264;color:#fff;border:2px solid #dc3264;height:47px}.donation_button_fundraiser{margin-top:20px;width:50%;margin-right:2%;color:#fff;background:#e6f4ff;border:2px solid #1c7bce;color:#1c7bce;padding:5px 45px 17px 45px;box-shadow:0 10px 12px 0 rgba(0,0,0,.1)!important}.donation_button_fundraiser img{position:absolute;width:24px;margin-left:-75px;margin-top:3px;}.donation_button_fundraiser .text-fundraiser{padding-top:8px;padding-left:28px;font-size:13px;font-weight:700}
    	.donation_button_fundraiser:hover {
      			background: <?php echo $color_hovernya; ?> !important;
      			box-shadow: 0px 18px 15px 0 rgba(0,0,0,.1) !important;
    	}
    	.copy_link_aff img { width: 20px; margin-top: 6px; margin-left: -65px; }
    	.fundraiser-loading{display:inline-block}.fundraiser-loading:after{content:" ";display:block;width:10px;height:10px;margin:0;border-radius:50%;border:3px solid #fff;border-color:<?php echo $button_color; ?> transparent <?php echo $button_color; ?> transparent;animation:fundraiser-loading 1.2s linear infinite;position:absolute;margin-top:-13px;margin-left:10px}@keyframes fundraiser-loading{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}
    	.fundraiser-hide{display:none}
		.section-image img{height:auto!important}#header-title{z-index:99}.section-box{border-radius:12px;background:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.02);border:1px solid #eef2f6}.scale_button:active{scale:.95}.add_info{font-size:12px;background:#fff;padding:6px 12px;border-radius:20px;margin-right:15px;margin-top:-5px;text-align:center;margin-bottom:15px;border:1.5px solid <?php echo $button_color;?>;color:<?php echo $button_color;?>;width:auto;display:inline-block;font-weight:600;transition:all 0.2s}.add_info:hover{background:<?php echo $button_color;?>;color:#fff}
		.btn-readmore{background-color:#fff;color:<?php echo $button_color;?>;cursor:pointer;font-size:12.5px;font-weight:600;border:1.5px solid <?php echo $button_color;?>;padding:6px 20px!important;border-radius:20px;box-shadow:0 3px 10px rgba(0,0,0,0.06);transition:all 0.2s;display:inline-block}.btn-readmore:hover{background-color:<?php echo $button_color;?>;color:#fff;border-color:<?php echo $button_color;?>}
		/* --- Compact & Classic Styling --- */
		.readmore-desc{max-height:<?php if($readmore_description=="1"){echo "160px";}else{echo 'default';}?>;position:relative;overflow:hidden;color:#334155;font-size:14.5px;line-height:1.75}.readmore-desc .box-button-readmore{position:absolute;bottom:0;left:0;width:100%;text-align:center;margin:0;padding-top:45px;padding-bottom:6px;background-image:linear-gradient(to bottom,rgba(255,255,255,0) 0%,rgba(255,255,255,0.92) 60%,#ffffff 100%)}.readmore-desc img{width:100%!important;border-radius:6px;margin:8px 0}
		.terbaru,.terbesar{font-size:12.5px;font-weight:600;padding:5px 14px;border-radius:20px;border:1.5px solid #e2e8f0;background:#f8fafc;color:#64748b;cursor:pointer;transition:all 0.2s ease;margin-right:6px;margin-top:10px;margin-left:0;width:auto;display:inline-block}.terbaru:hover,.terbesar:hover{border-color:<?php echo $button_color;?>;color:<?php echo $button_color;?>}.terbaru.btn-active,.terbesar.btn-active{background:<?php echo $button_color;?>;border-color:<?php echo $button_color;?>;color:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.12)!important}
		.donation_box{margin-top:10px}.donation_box2{margin-top:10px}
		.donation_inner_box{background:#ffffff!important;border:1px solid #edf2f7!important;border-radius:8px!important;padding:10px 14px!important;margin:6px 0!important;box-shadow:0 1px 3px rgba(0,0,0,0.02)!important;transition:all 0.2s ease}.donation_inner_box:hover{background:#f8fafc!important;border-color:#cbd5e1!important}
		.donation_name{font-size:13.5px!important;font-weight:700!important;color:#1e293b!important;display:flex!important;justify-content:space-between!important;align-items:center!important;margin-bottom:2px!important;text-align:left}
		.donation_time{font-size:11px!important;color:#94a3b8!important;font-weight:400!important;display:inline-flex!important;align-items:center!important;gap:3px!important;float:none!important}
		.donation_time .dashicons{font-size:12px!important;width:12px!important;height:12px!important;line-height:12px!important;padding-top:0!important}
		.donation_total{font-size:12.5px!important;color:#475569!important;margin-top:2px!important;line-height:1.4!important;text-align:left}
		.donation_total b{color:#0f172a!important;font-weight:700!important}
		.donation_comment{font-size:13px!important;color:#334155!important;line-height:1.55!important;background:#f8fafc!important;padding:8px 12px!important;border-radius:6px!important;margin:6px 0!important;border-left:3px solid #cbd5e1!important;font-style:italic!important;text-align:left}
		.donation_love{padding-top:4px!important;width:auto!important;display:inline-block!important;cursor:pointer}
		.fancy-button{display:inline-flex!important;align-items:center!important;background:#f1f5f9!important;border:1px solid #e2e8f0!important;padding:3px 10px!important;border-radius:14px!important;cursor:pointer!important;transition:all 0.2s ease!important}.fancy-button:hover{background:#e2e8f0!important}
		.fancy-button .box_love{display:flex!important;align-items:center!important;gap:5px!important}
		.fancy-button .box_love img{width:14px!important;height:14px!important;position:static!important;margin-top:0!important}
		.fancy-button .total_love{margin-left:0!important;font-size:11.5px!important;font-weight:600!important;color:#475569!important}
		#box-button-fundraiser{text-align:center;padding:20px 15px 25px 15px!important;background:<?php echo $colornya_soft;?>;background-image:linear-gradient(to top,<?php echo $colornya_soft;?> 0%,#fff 100%);border-radius:8px;margin-bottom:5px;border:1px solid rgba(0,0,0,0.04)}
		.timeline{border-left:2px solid #e2e8f0!important;margin-left:12px!important;padding-left:16px!important;position:relative!important;list-style:none!important}
		.timeline-milestone{position:relative!important;margin-bottom:18px!important;list-style:none!important}
		.timeline-milestone::before{content:''!important;position:absolute!important;left:-22px!important;top:5px!important;width:10px!important;height:10px!important;border-radius:50%!important;background:<?php echo $button_color;?>!important;border:2px solid #ffffff!important;box-shadow:0 0 0 2px <?php echo $button_color;?>!important}
		.timeline-action .date{font-size:11px!important;font-weight:600!important;color:#64748b!important;text-transform:uppercase!important;letter-spacing:0.5px!important;display:inline-block!important;margin-bottom:2px!important}
		.timeline-action .title{font-size:14.5px!important;font-weight:700!important;color:#1e293b!important;margin:2px 0 6px 0!important}
		.timeline-action .content{font-size:13px!important;color:#475569!important;line-height:1.6!important}
		.donation_box.black .donation_button button.load_data_donatur, .donation_box.black .donation_button button.load_doa_donatur, .donation_box.black .donation_button button.load_fundraiser, .donation_button button{background:#ffffff!important;color:#334155!important;font-size:12.5px!important;font-weight:600!important;padding:6px 22px!important;border-radius:20px!important;border:1.5px solid #cbd5e1!important;box-shadow:0 2px 6px rgba(0,0,0,0.04)!important;height:36px!important;transition:all 0.2s ease!important}
		.donation_box.black .donation_button button.load_data_donatur:hover, .donation_box.black .donation_button button.load_doa_donatur:hover, .donation_box.black .donation_button button.load_fundraiser:hover, .donation_button button:hover{background:#f8fafc!important;border-color:#94a3b8!important;color:#0f172a!important;box-shadow:0 3px 8px rgba(0,0,0,0.08)!important}
		@media only screen and (max-width:480px){.donation_button_fundraiser{width:100%}.box_terbaru .donation_inner_box,.box_terbesar .donation_inner_box{margin:6px 0}.donation_inner_box{margin:6px 0}.terbaru{margin-left:0}#box-button-fundraiser{padding-left:15px;padding-right:15px}.section-box.flying-button .donation_button_now2{padding:12px 10px}}
		.donation_inner_box {border-radius: 8px !important;}
		<?php 
		if($total_donasi->jumlah>999){
			echo ".container--tabs .nav-tabs > li > a { padding: 10px 10px !important; font-size:  12px; }";
		}else{
			echo ".container--tabs .nav-tabs > li > a { padding: 10px 10px !important; font-size:  13px; }";
		} ?>

		.container--tabs .nav-tabs > li > a { color: #23374d; font-weight: bold; }
		.container--tabs .nav-tabs > li.active > a, .container--tabs .nav-tabs > li.active > a:hover, .container--tabs .nav-tabs > li.active > a:focus { padding: 10px 12px !important; color:#ffffff; background: <?php echo $button_color;?>; border: 1px solid <?php echo $button_color;?>;}
		.timeline-milestone.is-current::before,.timeline-milestone.is-start::before{background-color:<?php echo $button_color;?>}

		@media only screen and (max-width: 768px) {
		  .whatsapp-float {
		    bottom: 20px;
		  }
		  .whatsapp-float.geser-dikit {
		  	bottom: 90px;
		  }
		}

        <?php
        if($id_login!='0'){
        	echo '.timeline-action.is-expandable .title { margin-bottom: 10px; } .timeline-action.is-expandable.is-expanded { padding-bottom: 40px; } ';
        }
        if($total_donasi->jumlah>100){ 
        echo '
	        @media only screen and (max-width:480px) {
	            .scrollable-tabs {
				  display: flex;
				  overflow-x: auto;
				  white-space: nowrap;
				  -webkit-overflow-scrolling: touch;
				}
		    }';
		}
        ?>

        /* --- Modern Campaign Navigation List Card Accordion --- */
        .dyk-nav-list-card{background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;margin-top:14px;margin-bottom:14px;box-shadow:0 1px 3px rgba(0,0,0,0.02);overflow:hidden;}
        .dyk-nav-item{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;text-decoration:none!important;color:inherit!important;border-bottom:1px solid #f3f4f6;transition:background 0.15s ease;cursor:pointer;background:#ffffff;user-select:none}
        .dyk-nav-item:last-child{border-bottom:none}
        .dyk-nav-item:hover,.dyk-nav-item.is-open{background:#f9fafb}
        .dyk-nav-item.is-open{border-bottom:1px solid #e5e7eb}
        .dyk-nav-item-left{display:flex;flex-direction:column;gap:4px;text-align:left}
        .dyk-nav-item-header{display:flex;align-items:center;gap:8px}
        .dyk-nav-item-title{font-size:15.5px!important;font-weight:700!important;color:#111827!important;line-height:1.3!important;margin:0!important}
        .dyk-nav-badge{background:#e0f2fe;color:#0284c7;font-size:12px;font-weight:700;padding:2px 10px;border-radius:9999px;line-height:1.4;display:inline-flex;align-items:center;justify-content:center}
        .dyk-nav-item-subtitle{font-size:12.5px;color:#6b7280;font-weight:400;display:flex;align-items:center;gap:6px;margin:0;line-height:1.4}
        .dyk-nav-item-arrow{display:flex;align-items:center;justify-content:center;color:#9ca3af;flex-shrink:0;margin-left:12px}
        .dyk-chevron{transition:transform 0.25s ease,color 0.2s ease}
        .dyk-nav-item.is-open .dyk-chevron{transform:rotate(90deg);color:#0284c7}
        .dyk-nav-collapse-content{background:#ffffff;border-bottom:1px solid #f3f4f6}
        .dyk-collapse-inner{padding:14px 16px 18px 16px;text-align:left}

        /* --- Unified Main Campaign Card (Gambar, Judul & Info Penggalangan Dana jadi satu) --- */
        .dyk-campaign-main-card {
            background: #ffffff !important;
            border-radius: 12px !important;
            overflow: hidden !important;
            margin-bottom: 12px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03) !important;
            border: 1px solid #eef2f6 !important;
            padding: 0 !important;
        }

        .dyk-campaign-main-card .section-image {
            position: relative !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            line-height: 0 !important;
            overflow: hidden !important;
        }

        .dyk-campaign-main-card .section-image img {
            width: 100% !important;
            height: auto !important;
            display: block !important;
            border-radius: 0 !important;
        }

        .dyk-hero-back-btn {
            position: absolute;
            top: 14px;
            left: 14px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(0,0,0,0.45);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            padding: 0;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }
        .dyk-hero-back-btn:hover {
            background: rgba(0,0,0,0.7);
            transform: scale(1.05);
        }
        .dyk-hero-back-btn svg { stroke: #ffffff; }

        /* Media Cover Styles (Gallery Slider & Video) */
        .dyk-campaign-slider {
            position: relative;
            width: 100%;
            overflow: hidden;
            background: #000;
            border-radius: 12px 12px 0 0;
        }
        .dyk-slider-track {
            display: flex;
            transition: transform 0.35s cubic-bezier(0.25, 1, 0.5, 1);
            width: 100%;
        }
        .dyk-slider-slide {
            min-width: 100%;
            width: 100%;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dyk-slider-slide img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
        }
        .dyk-slider-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(0,0,0,0.45);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border: none;
            color: #fff;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 5;
            transition: all 0.2s ease;
            padding: 0;
        }
        .dyk-slider-nav:hover {
            background: rgba(0,0,0,0.75);
            transform: translateY(-50%) scale(1.08);
        }
        .dyk-slider-prev { left: 12px; }
        .dyk-slider-next { right: 12px; }
        .dyk-slider-counter {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: rgba(0,0,0,0.55);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 12px;
            z-index: 5;
            letter-spacing: 0.5px;
        }
        .dyk-slider-dots {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
            z-index: 5;
        }
        .dyk-slider-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(255,255,255,0.45);
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .dyk-slider-dot.active {
            background: #ffffff;
            width: 18px;
            border-radius: 4px;
        }
        .dyk-campaign-video-cover {
            position: relative;
            width: 100%;
            overflow: hidden;
            background: #000;
            border-radius: 12px 12px 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: translate3d(0, 0, 0);
        }
        .dyk-video-parallax-inner {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: translate3d(0, 0, 0);
            will-change: transform;
        }
        .dyk-campaign-video-cover video {
            width: 100%;
            height: auto;
            max-height: 540px;
            display: block;
            object-fit: contain;
            background: #000;
            transform: scale(1.08);
            will-change: transform;
        }
        .dyk-campaign-video-cover .dyk-video-iframe-landscape {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%;
            height: 0;
            transform: scale(1.05);
            will-change: transform;
        }
        .dyk-campaign-video-cover .dyk-video-iframe-landscape iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        .dyk-campaign-video-cover .dyk-video-iframe-vertical {
            position: relative;
            width: 100%;
            max-width: 340px;
            aspect-ratio: 9 / 16;
            height: 540px;
            margin: 0 auto;
            transform: scale(1.05);
            will-change: transform;
        }
        .dyk-campaign-video-cover .dyk-video-iframe-vertical iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }

        .dyk-campaign-main-card #campaign-title {
            background: transparent !important;
            border-radius: 0 !important;
            padding: 18px 16px 14px 16px !important;
            margin: 0 !important;
            box-shadow: none !important;
            text-align: left !important;
            border: none !important;
        }

        #campaign-title .title h1 {
            font-size: 18px !important;
            font-weight: 800 !important;
            color: #1e293b !important;
            line-height: 1.35 !important;
            margin: 0 0 12px 0 !important;
            text-transform: uppercase;
            letter-spacing: -0.2px;
        }
        
        .dyk-stat-amount-large {
            font-size: 21px !important;
            font-weight: 800 !important;
            color: #10a8e5 !important;
            line-height: 1.2 !important;
            margin: 0 0 6px 0 !important;
            letter-spacing: -0.3px;
            text-align: left;
        }
        .dyk-stat-target-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 8px;
            line-height: 1.4;
        }
        .dyk-stat-target-left { text-align: left; }
        .dyk-stat-target-left strong { color: #1e293b; font-weight: 700; }
        .dyk-stat-target-right { text-align: right; color: #64748b; font-size: 13px; white-space: nowrap; }

        .dyk-progress-bar-modern {
            width: 100%;
            height: 7px;
            background: #e2e8f0;
            border-radius: 9999px;
            overflow: hidden;
            margin: 8px 0 14px 0;
        }
        .dyk-progress-bar-modern .dyk-progress-fill {
            height: 100%;
            background: #10a8e5;
            border-radius: 9999px;
            transition: width 0.4s ease;
        }

        /* 3-Column Navigation Bar */
        .dyk-stats-3col {
            display: flex;
            align-items: center;
            justify-content: space-around;
            border-top: 1px solid #f1f5f9;
            padding-top: 12px;
            margin-top: 6px;
        }
        .dyk-stat-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            text-decoration: none !important;
            color: inherit !important;
            border-right: 1px solid #f1f5f9;
            cursor: pointer;
            transition: background 0.15s ease;
            padding: 4px 0;
        }
        .dyk-stat-col:last-child { border-right: none; }
        .dyk-stat-col:hover { background: #f8fafc; border-radius: 6px; }
        .dyk-stat-col-top { display: flex; align-items: center; gap: 6px; }
        .dyk-stat-col-num { font-size: 15px; font-weight: 700; color: #1e293b; line-height: 1; }
        .dyk-stat-col-label { font-size: 12px; color: #64748b; font-weight: 500; line-height: 1.2; }

        /* Informasi Penggalangan Dana inside Unified Main Card */
        .dyk-campaign-main-card .dyk-info-penggalangan-section {
            background: transparent !important;
            border-radius: 0 !important;
            padding: 14px 16px 18px 16px !important;
            margin: 0 !important;
            box-shadow: none !important;
            border-top: 1px solid #f1f5f9 !important;
            text-align: left !important;
        }
        .dyk-info-heading {
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            margin: 0 0 10px 0 !important;
            line-height: 1.3 !important;
            text-align: left !important;
        }
        .dyk-penggalang-box {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 14px;
            background: #ffffff;
            text-align: left;
        }
        .dyk-penggalang-label {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
            text-align: left;
        }
        .dyk-penggalang-row { display: flex; align-items: center; gap: 12px; }
        .dyk-penggalang-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 1px solid #e2e8f0;
            display: inline-block;
        }
        .dyk-penggalang-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .dyk-penggalang-info { display: flex; flex-direction: column; gap: 2px; text-align: left; }
        .dyk-penggalang-name-wrap { display: flex; align-items: center; gap: 4px; text-align: left; }
        .dyk-penggalang-name {
            font-size: 14.5px !important;
            font-weight: 700 !important;
            color: #111827 !important;
            text-decoration: none !important;
            line-height: 1.2;
        }
        .dyk-penggalang-name:hover { color: #0284c7 !important; }
        .dyk-verified-icon, .verified_checklist {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            vertical-align: middle !important;
            flex-shrink: 0 !important;
            margin-left: 4px !important;
            line-height: 1 !important;
            cursor: pointer;
            position: relative;
        }
        .dyk-verified-icon svg, .verified_checklist svg { width: 15px !important; height: 15px !important; display: block !important; }
        .dyk-verified-icon::after, .verified_checklist::after {
            content: "Verified";
            position: absolute;
            bottom: calc(100% + 5px);
            left: 50%;
            transform: translateX(-50%) translateY(4px);
            background: #1e293b;
            color: #ffffff;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 8px;
            border-radius: 4px;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            z-index: 999;
        }
        .dyk-verified-icon::before, .verified_checklist::before {
            content: "";
            position: absolute;
            bottom: calc(100% + 1px);
            left: 50%;
            transform: translateX(-50%) translateY(4px);
            border: 4px solid transparent;
            border-top-color: #1e293b;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
            z-index: 999;
        }
        .dyk-verified-icon:hover::after, .verified_checklist:hover::after,
        .dyk-verified-icon:hover::before, .verified_checklist:hover::before {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        .dyk-penggalang-status { font-size: 12px; color: #64748b; line-height: 1.2; }

        /* --- Modern Sticky Top Header Navbar (#header-title) --- */
        #header-title {
            display: none !important;
            position: fixed !important;
            top: 0 !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: 100% !important;
            max-width: 520px !important;
            box-sizing: border-box !important;
            height: 52px !important;
            background: #ffffff !important;
            border-bottom: 1px solid #f1f5f9 !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important;
            z-index: 99999 !important;
            align-items: center !important;
            padding: 0 14px !important;
            margin: 0 !important;
            border-radius: 0 !important;
            gap: 10px !important;
            transition: all 0.25s ease !important;
        }

        #header-title.flying-header {
            display: flex !important;
        }

        .dyk-sticky-back-btn {
            background: transparent !important;
            border: none !important;
            cursor: pointer !important;
            padding: 6px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 50% !important;
            color: #1e293b !important;
            flex-shrink: 0 !important;
            transition: background 0.15s ease !important;
        }

        .dyk-sticky-back-btn:hover {
            background: #f1f5f9 !important;
        }

        #header-title .campaign-header-title {
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            margin: 0 !important;
            padding: 0 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            flex: 1 !important;
            text-align: left !important;
            display: block !important;
            position: static !important;
            line-height: 1.2 !important;
        }

        /* --- Modern Sticky Action Bar (#fixed-button) --- */
        #fixed-button {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            padding: 10px 14px !important;
            box-sizing: border-box !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 auto !important;
        }

        #fixed-button.flying-button,
        .section-box.flying-button {
            position: fixed !important;
            bottom: 0 !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: 100% !important;
            max-width: 520px !important;
            box-sizing: border-box !important;
            margin: 0 !important;
            border-radius: 0 !important;
            z-index: 9999 !important;
            background: #ffffff !important;
            box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08) !important;
            border-top: 1px solid #eef2f6 !important;
            padding: 10px 14px !important;
        }

        .dyk-sticky-btn-share {
            flex: 1 !important;
            max-width: 120px !important;
            height: 46px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            background: #ffffff !important;
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            color: #4b5563 !important;
            font-size: 13.5px !important;
            font-weight: 600 !important;
            padding: 0 10px !important;
            margin: 0 !important;
            box-sizing: border-box !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04) !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            text-decoration: none !important;
            outline: none !important;
            flex-shrink: 0 !important;
        }

        .dyk-sticky-btn-share:hover {
            background: #f9fafb !important;
            border-color: #9ca3af !important;
            color: #111827 !important;
        }

        .dyk-sticky-btn-share svg {
            color: #6b7280 !important;
            flex-shrink: 0 !important;
        }

        .dyk-sticky-btn-share:hover svg {
            color: #111827 !important;
        }

        #fixed-button a {
            flex: 2 !important;
            display: flex !important;
            text-decoration: none !important;
            box-sizing: border-box !important;
            margin: 0 !important;
            width: auto !important;
        }

        #fixed-button .donation_button_now2,
        .section-box.flying-button .donation_button_now2,
        #fixed-button.flying-button .donation_button_now2 {
            width: 100% !important;
            height: 46px !important;
            margin: 0 !important;
            padding: 0 16px !important;
            font-size: 14.5px !important;
            font-weight: 700 !important;
            border-radius: 8px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            line-height: 1.2 !important;
            box-sizing: border-box !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }

        #fixed-share-button {
            box-sizing: border-box !important;
            max-width: 520px !important;
            width: 100% !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            margin: 0 !important;
        }

        @media only screen and (max-width: 520px) {
            #fixed-button,
            #fixed-button.flying-button,
            .section-box.flying-button {
                width: 100% !important;
                max-width: 100% !important;
                left: 0 !important;
                right: 0 !important;
                transform: none !important;
                box-sizing: border-box !important;
            }
            #header-title,
            #header-title.flying-header {
                width: 100% !important;
                max-width: 100% !important;
                left: 0 !important;
                right: 0 !important;
                transform: none !important;
                box-sizing: border-box !important;
            }
            #fixed-share-button {
                width: 100% !important;
                max-width: 100% !important;
                left: 0 !important;
                right: 0 !important;
                transform: none !important;
            }
        }

        /* --- Modern Kabar Terbaru UI (Kitabisa style) --- */
        .dyk-kabar-container{padding:4px 2px}
        .dyk-kabar-item{border-bottom:1px solid #f1f5f9;padding-bottom:18px;margin-bottom:18px;text-align:left}
        .dyk-kabar-item:last-of-type{border-bottom:1px solid #f1f5f9}
        .dyk-kabar-author{display:flex;align-items:center;gap:10px;margin-bottom:8px}
        .dyk-kabar-avatar{width:38px;height:38px;border-radius:50%;overflow:hidden;background:#f1f5f9;flex-shrink:0;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center}
        .dyk-kabar-avatar img{width:100%;height:100%;object-fit:cover}
        .dyk-kabar-author-info{display:flex;flex-direction:column;gap:2px;text-align:left}
        .dyk-kabar-author-name{font-size:14px!important;font-weight:700!important;color:#111827!important;line-height:1.2}
        .dyk-kabar-time{font-size:12px;color:#64748b;line-height:1.2}
        .dyk-kabar-title{font-size:15.5px!important;font-weight:700!important;color:#0f172a!important;margin:8px 0 8px 0!important;line-height:1.4!important;text-align:left!important}
        .dyk-kabar-content{font-size:14px;color:#334155;line-height:1.65;text-align:left}
        .dyk-kabar-body{word-break:break-word}
        .dyk-kabar-body img{max-width:100%!important;height:auto!important;border-radius:8px;margin-top:10px;margin-bottom:10px;display:block}
        .dyk-kabar-body.is-truncated{display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden}
        .dyk-kabar-readmore{display:inline-block;margin-top:6px;color:#00aeef!important;font-size:13.5px!important;font-weight:600!important;text-decoration:none!important;cursor:pointer}
        .dyk-kabar-readmore:hover{text-decoration:underline!important}
        .dyk-kabar-milestone{display:flex;align-items:center;gap:10px;padding:12px 14px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;margin-top:14px;text-align:left}
        .dyk-kabar-milestone-icon{width:28px;height:28px;border-radius:50%;background:#e0f2fe;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .dyk-kabar-milestone-info{display:flex;flex-direction:column;gap:2px}
        .dyk-kabar-milestone-title{font-size:13.5px;font-weight:600;color:#1e293b}
        .dyk-kabar-milestone-date{font-size:12px;color:#64748b}

        /* --- Modern Doa-doa Orang Baik UI --- */
        .dyk-doa-container{width:100%;margin-top:16px;margin-bottom:20px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif}
        .dyk-doa-header{display:flex;align-items:center;justify-content:space-between;padding:0 4px 14px 4px;margin-bottom:4px}
        .dyk-doa-title-wrapper{display:flex;align-items:center;gap:8px}
        .dyk-doa-title{font-size:16px!important;font-weight:700!important;color:#111827!important;margin:0!important;line-height:1.3!important;text-align:left!important}
        .dyk-doa-count-badge{background:#e0f2fe;color:#0284c7;font-size:12px;font-weight:700;padding:2px 10px;border-radius:9999px;line-height:1.4;display:inline-flex;align-items:center;justify-content:center}
        .dyk-doa-arrow{display:flex;align-items:center;justify-content:center;color:#9ca3af;cursor:pointer;transition:color 0.2s ease}
        .dyk-doa-arrow:hover{color:#4b5563}
        .dyk-doa-card{background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,0.02);transition:box-shadow 0.2s ease,border-color 0.2s ease;text-align:left;position:relative}
        .dyk-doa-card:hover{box-shadow:0 3px 10px rgba(0,0,0,0.05);border-color:#d1d5db}
        .dyk-doa-card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
        .dyk-doa-user{display:flex;align-items:center;gap:10px}
        .dyk-doa-avatar{width:38px;height:38px;border-radius:50%;background:#f3f4f6;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#9ca3af;overflow:hidden}
        .dyk-doa-user-meta{display:flex;flex-direction:column;gap:2px;line-height:1.2;text-align:left}
        .dyk-doa-name{font-size:14px;font-weight:700;color:#111827;letter-spacing:-0.1px}
        .dyk-doa-time{font-size:12px;color:#9ca3af;font-weight:400}
        .dyk-doa-more{color:#9ca3af;display:flex;align-items:center;justify-content:center;padding:4px;border-radius:6px;cursor:pointer;transition:all 0.2s ease}
        .dyk-doa-more:hover{color:#4b5563;background:#f3f4f6}
        .dyk-doa-text{font-size:13.5px;line-height:1.6;color:#374151;margin-bottom:12px;word-break:break-word;font-weight:400;text-align:left}
        .dyk-doa-stats{font-size:12px;color:#6b7280;margin-bottom:10px;line-height:1.4;text-align:left}
        .dyk-doa-stats strong{font-weight:700;color:#111827}
        .dyk-doa-divider{height:1px;background:#f3f4f6;margin:8px 0 6px 0}
        .dyk-doa-actions{display:flex;align-items:center;width:100%}
        .dyk-doa-btn{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:8px 0;background:transparent;border:none;font-size:13px;font-weight:600;color:#6b7280;cursor:pointer;transition:all 0.2s ease;border-radius:6px;position:relative;user-select:none}
        .dyk-doa-btn:hover{color:#111827;background:#f9fafb}
        .dyk-doa-btn:focus{outline:none}
        .dyk-heart-icon{transition:transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275),fill 0.2s ease}
        .dyk-btn-amin.loved .dyk-heart-icon,.dyk-btn-amin:hover .dyk-heart-icon{transform:scale(1.15)}
        .dyk-btn-amin.loved{color:#ef4444!important}
        .dyk-btn-amin .plus1{position:absolute;top:-10px;right:30%;color:#ef4444;font-size:12px;font-weight:700;opacity:0;pointer-events:none}
        .dyk-btn-amin .plus1.show{opacity:1}
        .dyk-doa-loadmore-box{text-align:center;margin-top:14px;margin-bottom:10px}
        .dyk-doa-loadmore-btn{background:#ffffff!important;color:#374151!important;font-size:13px!important;font-weight:600!important;padding:8px 24px!important;border-radius:20px!important;border:1px solid #d1d5db!important;box-shadow:0 1px 3px rgba(0,0,0,0.04)!important;cursor:pointer;transition:all 0.2s ease;height:auto!important}
        .dyk-doa-loadmore-btn:hover{background:#f9fafb!important;border-color:#9ca3af!important;color:#111827!important}
        .dyk-toast{position:fixed;bottom:80px;left:50%;transform:translateX(-50%) translateY(20px);background:#1f2937;color:#ffffff;padding:10px 20px;border-radius:30px;font-size:13px;font-weight:500;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:999999;opacity:0;transition:all 0.3s ease;pointer-events:none}
        .dyk-toast.show{transform:translateX(-50%) translateY(0);opacity:1}
        .dyk-doa-empty{text-align:center;padding:30px 20px;background:#ffffff;border:1px dashed #e5e7eb;border-radius:12px}
        .dyk-doa-empty img{width:80px;margin-bottom:10px;opacity:0.8}
        .dyk-doa-empty p{color:#9ca3af;font-size:13.5px;margin:0}
	</style>
	
	<script>
		!function(e,t){"object"==typeof exports&&"object"==typeof module?module.exports=t():"function"==typeof define&&define.amd?define([],t):"object"==typeof exports?exports.Ukiyo=t():e.Ukiyo=t()}(self,(function(){return function(){"use strict";var e={d:function(t,i){for(var r in i)e.o(i,r)&&!e.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:i[r]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t={};e.d(t,{default:function(){return h}});var i=function(e){return new Promise((function(t,i){var r=new Image;r.onload=function(){return t(r)},r.onerror=function(e){return i(e)},r.src=e}))};function r(e,t){(null==t||t>e.length)&&(t=e.length);for(var i=0,r=new Array(t);i<t;i++)r[i]=e[i];return r}
function n(e,t){var i=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),i.push.apply(i,r)}
return i}
function s(e){for(var t=1;t<arguments.length;t++){var i=null!=arguments[t]?arguments[t]:{};t%2?n(Object(i),!0).forEach((function(t){o(e,t,i[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(i)):n(Object(i)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(i,t))}))}
return e}
function o(e,t,i){return t in e?Object.defineProperty(e,t,{value:i,enumerable:!0,configurable:!0,writable:!0}):e[t]=i,e}
function l(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}
function a(e,t){for(var i=0;i<t.length;i++){var r=t[i];r.enumerable=r.enumerable||!1,r.configurable=!0,"value" in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}
var set_margintop=!1;var h=function(){function e(t){var r=this,n=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{};if(l(this,e),t){var o={scale:1.5,speed:1.5,wrapperClass:null,willChange:!1},a=t.getAttribute("data-u-scale"),h=t.getAttribute("data-u-speed"),p=t.getAttribute("data-u-willchange");if(this.element=t,this.wrapper=document.createElement("div"),this.options=s(s({},o),n),null!==a&&(this.options.scale=a),null!==h&&(this.options.speed=h),null!==p&&(this.options.willChange=!0),this.isIMGtag="img"===this.element.tagName.toLowerCase(),this.overflow=null,this.observer=null,this.requestId=null,this.timer=null,this.reset=this.reset.bind(this),this.isInit=!1,this.isIMGtag){var u=this.element.getAttribute("src");i(u).then((function(e){r._init()}))}else this._init()}}
var t,n;return t=e,(n=[{key:"_init",value:function(){this.isInit||(this._setupElements(),this._observer(),this._addEvent(),this.isInit=!0)}},{key:"_setupElements",value:function(){this._setStyles(!0);var e=this.element.getAttribute("data-u-wrapper-class");if(this.options.wrapperClass||e){var t=e||this.options.wrapperClass;this.wrapper.classList.add(t)}
var i=this.element.closest("picture");null!==i?(i.parentNode.insertBefore(this.wrapper,i),this.wrapper.appendChild(i)):(this.element.parentNode.insertBefore(this.wrapper,this.element),this.wrapper.appendChild(this.element))}},{key:"_setStyles",value:function(e){var t=this.element.clientHeight,i=this.element.clientWidth,r=document.defaultView.getComputedStyle(this.element),n="absolute"===r.position;this.overflow=t-t*this.options.scale,"0px"===r.marginTop&&"0px"===r.marginBottom||(this.wrapper.style.marginTop=r.marginTop,this.wrapper.style.marginBottom=r.marginBottom,this.element.style.marginTop="0",this.element.style.marginBottom="0"),"auto"!==r.inset&&(this.wrapper.style.top=r.top,this.wrapper.style.right=r.right,this.wrapper.style.bottom=r.bottom,this.wrapper.style.left=r.left,this.element.style.top="0",this.element.style.right="0",this.element.style.bottom="0",this.element.style.left="0"),"none"!==r.transform&&(this.wrapper.style.transform=r.transform),"auto"!==r.zIndex&&(this.wrapper.style.zIndex=r.zIndex),this.wrapper.style.position=n?"absolute":"relative",e&&(this.wrapper.style.width="100%",this.wrapper.style.overflow="hidden",this.element.style.display="block",this.element.style.overflow="hidden",this.element.style.backfaceVisibility="hidden","0px"!==r.padding&&(this.element.style.padding="0"),this.isIMGtag?this.element.style.objectFit="cover":this.element.style.backgroundPosition="center"),n&&(this.wrapper.style.width=i+"px",this.element.style.width="100%"),"none"!==r.maxHeight&&(this.wrapper.style.maxHeight=r.maxHeight,this.element.style.maxHeight="none"),"0px"!==r.minHeight&&(this.wrapper.style.minHeight=r.minHeight,this.element.style.minHeight="none"),this.wrapper.style.height=t+"px",this.element.style.height=t*this.options.scale+"px"}},{key:"_observer",value:function(){this.observer=new IntersectionObserver(this._observerCallback.bind(this),{root:null,rootMargin:"0px",threshold:0}),this.observer.observe(this.wrapper)}},{key:"_observerCallback",value:function(e){var t=this;e.forEach((function(e){e.isIntersecting?(t.isVisible=!0,t._update()):(t.isVisible=!1,t._cancel())}))}},{key:"_update",value:function(){this._setPosition(),this.requestId=window.requestAnimationFrame(this._update.bind(this))}},{key:"_setPosition",value:function(){this.options.willChange&&"transform"!==this.element.style.willChange&&(this.element.style.willChange="transform"),this.element.style.transform="translate3d(0 , ".concat(this._getTranslate(),"px , 0)"),this._checkMarginTop()}},{key:"_checkMarginTop",value:function(){if(set_margintop==!1){this.element.style.marginTop="".concat(this._getTranslate()*-1,"px")
set_margintop=!0;$('.parallax-wrapper img.parallax').attr('data-mgtop',this._getTranslate()*-1+"px")}}},{key:"_getTranslate",value:function(){var e=Math.abs(this.overflow),t=this._getProgress()/100,i=this.overflow+e*t*this.options.speed;return Math.round(i)}},{key:"_getProgress",value:function(){var e=window.innerHeight,t=this.wrapper.offsetHeight,i=window.pageYOffset||document.documentElement.scrollTop||document.body.scrollTop||0,r=(i+e-(this.wrapper.getBoundingClientRect().top+i))/((e+t)/100);return Math.min(100,Math.max(0,r))}},{key:"_cancel",value:function(){this.requestId&&(this.options.willChange&&(this.element.style.willChange="auto"),window.cancelAnimationFrame(this.requestId))}},{key:"_addEvent",value:function(){navigator.userAgent.match(/(iPhone|iPad|iPod|Android)/)?window.addEventListener("orientationchange",this.resize.bind(this)):window.addEventListener("resize",this.resize.bind(this))}},{key:"resize",value:function(){clearTimeout(this.timer),this.timer=setTimeout(this.reset,450)}},{key:"reset",value:function(){this.wrapper.style.height="",this.wrapper.style.width="",this.wrapper.style.position="",this.element.style.height="",this.element.style.width="","0px"!==this.wrapper.style.margin&&(this.wrapper.style.margin="",this.element.style.margin=""),"auto"!==this.wrapper.style.inset&&(this.wrapper.style.top="",this.wrapper.style.right="",this.wrapper.style.bottom="",this.wrapper.style.left="",this.element.style.top="",this.element.style.right="",this.element.style.bottom="",this.element.style.left=""),"none"!==this.wrapper.style.transform&&(this.wrapper.style.transform="",this.element.style.transform=""),"auto"!==this.wrapper.style.zIndex&&(this.wrapper.style.zIndex=""),this._setStyles(),this._setPosition(),this._checkMarginTop()}},{key:"destroy",value:function(){var e,t;this._cancel(),this.observer.disconnect(),this.wrapper.removeAttribute("style"),this.element.removeAttribute("style"),(e=this.wrapper).replaceWith.apply(e,function(e){if(Array.isArray(e))return r(e)}(t=this.wrapper.childNodes)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(t)||function(e,t){if(e){if("string"==typeof e)return r(e,t);var i=Object.prototype.toString.call(e).slice(8,-1);return"Object"===i&&e.constructor&&(i=e.constructor.name),"Map"===i||"Set"===i?Array.from(e):"Arguments"===i||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(i)?r(e,t):void 0}}(t)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()),this.isInit=!1}}])&&a(t.prototype,n),e}();return t.default}()}))
	</script>

	<?php 
    if (!empty($fb_pixel) && strpos($fb_pixel, ',') !== false ) {

        $array_pixel  = (explode(",", $fb_pixel));
        $count = count($array_pixel);
        $i = 1; ?>
         
    <script>
	!function(f,b,e,v,n,t,s)
	{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
	n.callMethod.apply(n,arguments):n.queue.push(arguments)};
	if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
	n.queue=[];t=b.createElement(e);t.async=!0;
	t.src=v;s=b.getElementsByTagName(e)[0];
	s.parentNode.insertBefore(t,s)}(window, document,'script',
	'https://connect.facebook.net/en_US/fbevents.js');
	<?php foreach ($array_pixel as $values){
        	$pixel_id = $values;
        	?> 
	fbq('init', '<?php echo $pixel_id; ?>');
	<?php } ?>
	fbq('track', '<?php echo $event_1; ?>');
	</script>
        
    <?php 

    }elseif($fb_pixel==''){
        $pixel_id = "";
    }else{
        $pixel_id = $fb_pixel;
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
	fbq('init', '<?php echo $pixel_id; ?>');
	fbq('track', '<?php echo $event_1; ?>');
	</script>

        <?php
    }
    ?>
    <?php if($gtm_id!=''){ ?>
    
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?php echo $gtm_id;?>');</script>
    <!-- End Google Tag Manager -->

    <?php } ?>

    <?php if($tiktok_pixel!=''){ ?>
    <script>
	!function (w, d, t) {
	  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};

	  ttq.load('<?php echo $tiktok_pixel; ?>');
	  ttq.page();
	  ttq.track('<?php echo $event_1; ?>', {
		  content_id: '<?php echo $row->campaign_id; ?>',
		  content_type: 'product',
		  content_name: '<?php echo $row->title; ?>',
		  value: 0,
		  currency: '<?php echo $currency?>'
	  });
	}(window, document, 'ttq');
	</script>
	<?php } ?>

</head>
<body>
	<?php
	function isMobile() {
	    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
	}

	$campaign_title = $row->title;
	if(strlen($campaign_title)>40){
		if(isMobile()){
		    $fix_title = substr($campaign_title, 0, 41).'...';
		}
		else {
		    $fix_title = substr($campaign_title, 0, 50).'...';
		}
	}else{
		$fix_title = $campaign_title;
	}

	$cap = get_user_meta( wp_get_current_user()->ID, $wpdb->get_blog_prefix() . 'capabilities', true );
    $roles = array_keys((array)$cap);
    $role = $roles[0];

    $set_location = '';
    if($row->location_name!=null){
    	if($row->location_gmaps!=null){
    		$set_location = '<a href="'.$row->location_gmaps.'" target="_blank" style="text-decoration:none;"><span class="d_map"><img alt="Image" src="'.plugin_dir_url( __FILE__ ) . "assets/images/maps.png".'"><div class="loc_name">'.$row->location_name.'</div></span></a>';
    	}else{
    		$set_location = '<span class="d_map"><img alt="Image" src="'.plugin_dir_url( __FILE__ ) . 'assets/images/maps.png"><div class="loc_name">'.$row->location_name.'</div></span>';
    	}
    }

    // cek dia itu target >= 1
    // cek lagi apakah yang didapat >= target
    // $total_donasi->total >= $row->target

    $donasi_terpenuhi = false;
    if($row->target>=1){
    	if($total_donasi->total >= $row->target){
    		if($limitted_donation_button=='1'){
    			$donasi_terpenuhi = true;
    		}
    	}
    }

	?>
	<div id="header-title" class="section-box">
		<button type="button" class="dyk-sticky-back-btn" onclick="if(history.length > 1){history.back();}else{window.location.href='<?php echo $home_url; ?>';}" title="Kembali">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
		</button>
		<span class="campaign-header-title"><?php echo $fix_title; ?></span>
	<?php if($id_login!=null) 

		$action = false;
		if($role=='donatur'){
			if($id_login==$row->user_id){
				$action = true;
			}else{
				$action = false;
			}
		}else{
			if($role=='administrator'){
				$action = true;
			}else{
				$action = false;
			}
		}

	{ ?>

		<?php if($action==true){ ?>

		<a href="<?php echo admin_url('admin.php?page=donasiyuk_data_campaign&action=edit&id=').$row->campaign_id ?>" style="color:#444; margin-left:auto;"><div style="font-size: 13px;background: #f1f5f9;padding: 5px 10px;border-radius: 4px;display:flex;align-items:center;gap:4px;"><img alt="" src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/pencil.png'; ?>" style="width:11px;">Edit</div></a>

	<?php } } ?>
	</div>

	<!-- Unified Main Campaign Card (Gambar, Nama Donasi & Informasi Penggalangan Dana jadi satu) -->
	<div class="section-box dyk-campaign-main-card">
		<?php
		$c_media_type   = isset($row->media_type) ? $row->media_type : 'image';
		$c_gallery_urls = isset($row->gallery_urls) ? $row->gallery_urls : '';
		$c_video_url    = isset($row->video_url) ? $row->video_url : '';
		$c_cover_img    = !empty($row->image_url) ? $row->image_url : plugin_dir_url( __FILE__ ).'admin/images/cover_donasiyuk.jpg';

		if($c_media_type == 'video' && !empty($c_video_url)){
			$video_embed = '';
			$is_shorts = (strpos($c_video_url, 'shorts/') !== false || strpos($c_video_url, 'tiktok.com') !== false);

			if(strpos($c_video_url, 'youtube.com') !== false || strpos($c_video_url, 'youtu.be') !== false){
				preg_match('/(?:v=|youtu\.be\/|embed\/|shorts\/)([a-zA-Z0-9_-]{11})/', $c_video_url, $yt_matches);
				if(!empty($yt_matches[1])){
					$iframe_cls = $is_shorts ? 'dyk-video-iframe-vertical' : 'dyk-video-iframe-landscape';
					$video_embed = '<div class="'.$iframe_cls.'"><iframe src="https://www.youtube.com/embed/'.$yt_matches[1].'?autoplay=1&mute=1&playsinline=1&loop=1&playlist='.$yt_matches[1].'&rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
				}
			} elseif(strpos($c_video_url, 'vimeo.com') !== false){
				preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $c_video_url, $vm_matches);
				if(!empty($vm_matches[1])){
					$video_embed = '<div class="dyk-video-iframe-landscape"><iframe src="https://player.vimeo.com/video/'.$vm_matches[1].'?autoplay=1&muted=1&playsinline=1&loop=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>';
				}
			}
			?>
			<div class="dyk-campaign-video-cover" id="dyk_video_cover">
				<button type="button" class="dyk-hero-back-btn" onclick="if(history.length > 1){history.back();}else{window.location.href='<?php echo $home_url; ?>';}" title="Kembali">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
				</button>
				<div class="dyk-video-parallax-inner" id="dyk_video_parallax">
					<?php if(!empty($video_embed)){
						echo $video_embed;
					} else { ?>
						<video autoplay muted playsinline loop controls preload="auto" poster="<?php echo esc_url($c_cover_img); ?>">
							<source src="<?php echo esc_url($c_video_url); ?>" type="video/mp4">
							Browser Anda tidak mendukung tag video.
						</video>
					<?php } ?>
				</div>
			</div>
		<?php } elseif($c_media_type == 'gallery' && !empty($c_gallery_urls)) {
			$gallery_items = json_decode($c_gallery_urls, true);
			if(is_array($gallery_items) && count($gallery_items) > 1){
				$total_slides = count($gallery_items);
				?>
				<div class="dyk-campaign-slider" id="dyk_campaign_slider">
					<button type="button" class="dyk-hero-back-btn" onclick="if(history.length > 1){history.back();}else{window.location.href='<?php echo $home_url; ?>';}" title="Kembali">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
					</button>
					<div class="dyk-slider-track">
						<?php foreach($gallery_items as $idx => $g_img){ ?>
							<div class="dyk-slider-slide">
								<img alt="<?php echo esc_attr($campaign_title); ?>" src="<?php echo esc_url($g_img); ?>">
							</div>
						<?php } ?>
					</div>
					<button type="button" class="dyk-slider-nav dyk-slider-prev" title="Sebelumnya">&#10094;</button>
					<button type="button" class="dyk-slider-nav dyk-slider-next" title="Selanjutnya">&#10095;</button>
					<div class="dyk-slider-counter"><span class="dyk-current-slide">1</span> / <?php echo $total_slides; ?></div>
					<div class="dyk-slider-dots">
						<?php for($d=0; $d<$total_slides; $d++){ ?>
							<span class="dyk-slider-dot <?php if($d===0) echo 'active'; ?>" data-index="<?php echo $d; ?>"></span>
						<?php } ?>
					</div>
				</div>
			<?php } else { ?>
				<div class="section-image">
					<button type="button" class="dyk-hero-back-btn" onclick="if(history.length > 1){history.back();}else{window.location.href='<?php echo $home_url; ?>';}" title="Kembali">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
					</button>
					<img alt="Image" class="parallax" src="<?php echo esc_url($c_cover_img); ?>">
				</div>
			<?php } ?>
		<?php } else { ?>
			<div class="section-image">
				<button type="button" class="dyk-hero-back-btn" onclick="if(history.length > 1){history.back();}else{window.location.href='<?php echo $home_url; ?>';}" title="Kembali">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
				</button>
				<img alt="Image" class="parallax" src="<?php echo esc_url($c_cover_img); ?>">
			</div>
		<?php } ?>

		<div id="campaign-title">
			<div class="title"><h1><?php echo $campaign_title; ?></h1></div>
			<?php if($row->publish_status=='0'){?>
			<p style="font-size: 13px;color: #ababab;">Campaign on Drafts</p>
			<?php } elseif($row->publish_status=='2'){?>
			<p style="font-size: 13px;color: #C8D0DD;">Campaign on Status Review</p>
			<?php } ?>
			<?php echo $set_location; ?>

			<div class="dyk-stat-amount-large"><?php echo $show_currency; ?><?php echo number_format_currency($total_donasi->total); ?></div>

			<div class="dyk-stat-target-row">
				<div class="dyk-stat-target-left">
					<?php if($row->target==0){ ?>
						<span>dan masih terus dikumpulkan</span>
					<?php } else { ?>
						<span>Terkumpul dari <strong><?php echo $show_currency; ?><?php echo number_format_currency($row->target); ?></strong></span>
					<?php } ?>
				</div>
				<div class="dyk-stat-target-right">
					<span><?php echo $sisa_waktu; ?></span>
				</div>
			</div>

			<div class="dyk-progress-bar-modern">
				<div class="dyk-progress-fill" style="width:<?php echo $persen_width; ?>%;"></div>
			</div>

			<?php
			$dyk_count_kabar_top = !empty($campaign_update) && is_array($campaign_update) ? count($campaign_update) : 0;
			$dyk_count_donasi_top = !empty($total_donasi) && isset($total_donasi->jumlah) ? (int)$total_donasi->jumlah : 0;
			$dyk_count_fundraiser_top = !empty($all_fundraiser) && is_array($all_fundraiser) ? count($all_fundraiser) : 0;
			?>
			<div class="dyk-stats-3col">
				<a href="javascript:void(0);" class="dyk-stat-col dyk-trigger-accordion" data-target="#dyk-collapse-donasi">
					<div class="dyk-stat-col-top">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="#10A8E5"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
						<span class="dyk-stat-col-num"><?php echo $dyk_count_donasi_top; ?></span>
					</div>
					<div class="dyk-stat-col-label">Donasi</div>
				</a>
				<a href="javascript:void(0);" class="dyk-stat-col dyk-trigger-accordion" data-target="#dyk-collapse-kabar">
					<div class="dyk-stat-col-top">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10A8E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
						<?php if($dyk_count_kabar_top > 0){ ?><span class="dyk-stat-col-num"><?php echo $dyk_count_kabar_top; ?></span><?php } ?>
					</div>
					<div class="dyk-stat-col-label">Kabar Terbaru</div>
				</a>
				<a href="javascript:void(0);" class="dyk-stat-col dyk-trigger-accordion" data-target="<?php echo (isset($fundraiser_on) && $fundraiser_on=='1') ? '#dyk-collapse-fundraiser' : '#dyk-collapse-donasi'; ?>">
					<div class="dyk-stat-col-top">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10A8E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><path d="M6 16h4"/><path d="M15 14l2 2-2 2"/></svg>
						<?php if(isset($fundraiser_on) && $fundraiser_on=='1' && $dyk_count_fundraiser_top > 0){ ?><span class="dyk-stat-col-num"><?php echo $dyk_count_fundraiser_top; ?></span><?php } ?>
					</div>
					<div class="dyk-stat-col-label"><?php echo (isset($fundraiser_on) && $fundraiser_on=='1') ? 'Fundraiser' : 'Pencairan Dana'; ?></div>
				</a>
			</div>

			<?php if(isset($hasil->invert) && $hasil->invert==true){ ?>
			<div class="section-button button-disabled" style="margin-top:14px;"><a href="javascript:;"><button class="donation_button_now"><?php echo $text1; ?></button></a></div>
			<?php } elseif(isset($row->publish_status) && $row->publish_status=='3'){ ?>
			<div class="section-button button-disabled" style="margin-top:14px;"><a href="javascript:;"><button class="donation_button_now"><?php echo $text1; ?></button></a></div>
			<?php } elseif(isset($donasi_terpenuhi) && $donasi_terpenuhi==true){ ?>
			<div class="section-button button-disabled" style="margin-top:14px;"><a href="javascript:;"><button class="donation_button_now"><?php echo $text2; ?> Terpenuhi</button></a></div>
			<?php }else{ ?>
				<?php if (!empty($row->external_link_button)) { ?>
					<div class="section-button" style="margin-top:14px;"><a href="<?php echo $external_link;?>" target="_parent"><button class="donation_button_now scale_button" style="background:<?php echo $button_color;?>;border-color:<?php echo $button_color;?>"><?php if(isset($option_baznasjabar_link) && $option_baznasjabar_link=='1'){ ?><img alt="logo" src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/garuda.png'; ?>" style="width:27px;position: absolute;margin-top:-4px;margin-left: -10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php } ?><?php if(isset($option_whatsapp_link) && $option_whatsapp_link=='1'){ ?><img alt="logo" src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/whatsapp_icon.png'; ?>" style="width:22px;position: absolute;margin-top:-2px;margin-left: -10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php } ?><?php echo $text1; ?></button></a></div>
				<?php }else{ ?>
					<div class="section-button" style="margin-top:14px;"><a href="<?php echo $current_url;?>/<?php echo $page_donate; ?><?php echo $link_ref_aff; ?>"><button class="donation_button_now scale_button" style="background:<?php echo $button_color;?>;border-color:<?php echo $button_color;?>"><?php echo $text1; ?></button></a></div>
				<?php } ?>
			<?php } ?>
		</div>

		<!-- Informasi Penggalangan Dana Section -->
		<?php
		$dyk_user_randid = isset($profile[0]->user_randid) ? $profile[0]->user_randid : '';
		$dyk_user_type = isset($profile[0]->user_type) ? $profile[0]->user_type : '';
		$dyk_user_verif = isset($profile[0]->user_verification) ? $profile[0]->user_verification : '';
		?>
		<div class="dyk-info-penggalangan-section">
			<h3 class="dyk-info-heading">Informasi Penggalangan Dana</h3>
			<div class="dyk-penggalang-box">
				<div class="dyk-penggalang-label">Penggalang Dana</div>
				<div class="dyk-penggalang-row">
					<a href="<?php echo $home_url;?>/profile/<?php echo $dyk_user_randid; ?>" class="dyk-penggalang-avatar">
						<img alt="Image" src="<?php echo $profile_photo; ?>">
					</a>
					<div class="dyk-penggalang-info">
						<div class="dyk-penggalang-name-wrap">
							<a href="<?php echo $home_url;?>/profile/<?php echo $dyk_user_randid; ?>" class="dyk-penggalang-name">
								<?php echo ($dyk_user_type=='org') ? str_replace("\\", "", $fullname) : $fullname; ?>
							</a>
							<?php if($dyk_user_verif=='1'){ ?>
								<span class="dyk-verified-icon" title="Verified">
									<svg width="15" height="15" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path fill-rule="evenodd" clip-rule="evenodd" d="M10.407 1.689c-.293-1.585-2.471-1.585-2.764 0-.207 1.124-1.517 1.577-2.328.804l-.126-.12c-1.121-1.068-2.83.273-2.157 1.693.485 1.021-.316 2.195-1.402 2.052l-.103-.014C.031 5.906-.607 8.022.73 8.751c.967.528.967 1.97 0 2.498-1.336.729-.698 2.845.798 2.647l.103-.014c1.086-.143 1.887 1.03 1.402 2.052-.674 1.42 1.036 2.761 2.157 1.693l.126-.12c.81-.773 2.12-.32 2.328.804.293 1.585 2.471 1.585 2.764 0 .207-1.124 1.517-1.577 2.328-.804l.126.12c1.121 1.068 2.83-.273 2.157-1.693-.485-1.021.316-2.195 1.402-2.052l.103.014c1.496.198 2.134-1.918.798-2.647-.968-.528-.968-1.97 0-2.498 1.336-.729.698-2.845-.798-2.647l-.103.014c-1.086.143-1.887-1.03-1.402-2.052.673-1.42-1.036-2.761-2.157-1.692l-.126.12c-.81.772-2.12.32-2.328-.805Zm1.534 5.665c.452.337.544.975.204 1.424L9.21 12.443a1.026 1.026 0 0 1-1.543.109L6 10.814c-.4-.397-.4-1.042 0-1.44a1.029 1.029 0 0 1 1.449 0l.83.909 2.228-2.726c.339-.45.98-.54 1.434-.203Z" fill="#10A8E5"></path>
									</svg>
								</span>
							<?php } ?>
						</div>
						<div class="dyk-penggalang-status">
							<?php if($dyk_user_type=='org' && $dyk_user_verif=='1') { ?>
								<span>Verified Organization</span>
							<?php } elseif($dyk_user_type=='personal' && $dyk_user_verif=='1') { ?>
								<span>Verified User</span>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div><!-- end .dyk-campaign-main-card -->

	<?php if($label_tab=="tab") { ?>
	<div class="section-box" id="tab-donasiyuk">
		<div class="container--tabs" id="info-update">
			<section class="row">
				<?php 
				$jumlah_info = 0;
			  	foreach ($campaign_update as $getdata) { 
			  		$jumlah_info++;
			  	}
			  	if($jumlah_info>=1){
			  		$jumlah_infonya = '('.$jumlah_info.')';
			  	}else{
			  		$jumlah_infonya = '';
			  	}
			  	?>

				<ul class="nav nav-tabs scrollable-tabs">
					<li class="active"><a href="#tab-1">Keterangan</a></li>
					<li class=""><a href="#tab-2"><?php echo get_langArray('f_campaign_title3');?> <?php echo $jumlah_infonya; ?></a></li>
					<li class=""><a href="#tab-3"><?php echo $donatur_title; ?> <?php if($total_donasi->jumlah!=0){echo "($total_donasi->jumlah)";} ?></a></li>
				</ul>
				<div class="tab-content">
					<div id="tab-1" class="tab-pane active"> 
						<div class="col-md-10">
							
							<div class="readmore-desc">
								<?php echo $information; ?>

								<?php if($readmore_description=='1' ){ ?>
								<p class="box-button-readmore">
							        <a class="button btn-readmore" href="#"><?php echo get_langArray('f_campaign_button1');?> ▾</a>
							    </p>
								<?php } ?>

							</div>

						</div>
					</div> 
					<div id="tab-2" class="tab-pane">
						<div class="col-md-10">
							<?php

						    $dt = !empty($row->created_at) ? new DateTime($row->created_at) : new DateTime();
							$m = $dt->format('F');

						    if (strpos($m, 'January') !== false ) { $m = 'Januari'; }
						    elseif(strpos($m, 'February') !== false ) { $m = 'Februari'; }

						    $dt_published = $m.', '.$dt->format('j Y');

						    ?>

							<?php if($campaign_update==null){ ?>

					    	<div id="kabar-terbaru-donasi">
								  <ul class="timeline" style="margin-top: 50px;">
								  	<?php if($action==true){ ?>
								    <li class="timeline-milestone is-current" style="height:60px;">
								      	<div class="timeline-action" style="width: 100px;">
										<a href="<?php echo admin_url('admin.php?page=donasiyuk_data_campaign&action=info_update&id=').$row->campaign_id ?>" style="text-decoration:none;"><div class="add_info">Add Info</div></a>
										 </div>
								    </li>
									<?php } ?>
									<li class="timeline-milestone is-start" style="height: 50px;">
								      <div class="timeline-action">
								      	<span class="date"><?php echo $dt_published; ?></span>
								        <h3 class="title">Campaign is published</h3>
								      </div>
								    </li>
								  </ul>
					    	</div>

					    	<?php }else{?>
					    	<div id="kabar-terbaru-donasi">
								  
								  <ul class="timeline" style="margin-top: 50px;">

								  	<?php if($action==true){ ?>
								    <li class="timeline-milestone is-current" style="height:60px;">
								      	<div class="timeline-action" style="width: 100px;">
										<a href="<?php echo admin_url('admin.php?page=donasiyuk_data_campaign&action=info_update&id=').$row->campaign_id ?>" style="text-decoration:none;"><div class="add_info">Add Info</div></a>
										 </div>
								    </li>
									<?php } ?>

								  	<?php 
								  	foreach ($campaign_update as $value) { 

									  	$readtime = new donasiyuk_readtime();
										$time_update = $readtime->time_donation($value->created_at);

										$dt = new DateTime($value->created_at);
									    $m = $dt->format('F');

									    if (strpos($m, 'January') !== false ) { $m = 'Januari'; }
									    elseif(strpos($m, 'February') !== false ) { $m = 'Februari'; }

									    $dt = $m.', '.$dt->format('j Y');

								  	?>
									    <li class="timeline-milestone is-current">
									      <div class="timeline-action is-expandable expanded is-expanded">
										        <span class="date"><?php echo $dt; ?></span>
										        <h3 class="title"><?php echo wp_strip_all_tags($value->title); ?></h3>
										        <div class="content">
										        <?php
										        	$information_update = str_replace("'", "&#39;", $value->information); // petik 1
											        // $information_update = str_replace('"', "&#34;", $information_update); // petik 2
												    $information_update = str_replace('../wp-content', get_site_url().'/wp-content', $information_update);

										        	echo $information_update; 
										        ?>
										        
										        <?php if($action==true){ ?>
										        <a href="<?php echo admin_url('admin.php?page=donasiyuk_data_campaign&action=info_update&id=').$row->campaign_id.'&infoid='.$value->id ?>" style="text-decoration:none;width:90px;float: left;">
										        <div style="margin-bottom: 10px;text-align:left;background: #e2eef7;width: 60px;border-radius: 4px;padding: 5px 2px;font-size: 12px;padding-left: 18px;"><img alt="" src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/pencil.png'; ?>" style="width:10px;margin-right: 5px;">Edit</div>
											    </a>
										        <?php } ?>

										        </div>
									      </div>
									    </li>
									<?php } ?>
									<li class="timeline-milestone is-start" style="height: 50px;">
								      <div class="timeline-action">
								      	<span class="date"><?php echo $dt_published; ?></span>
								        <h3 class="title">Campaign is published</h3>
								      </div>
								    </li>

								    
									 

								  </ul>
					    	</div>
					    	<?php } ?>
						</div>
					</div>
					<div id="tab-3" class="tab-pane">
						<div class="col-md-10">

							<!-- donation -->
							<?php if($donasi!=null){ ?>
							<button class="terbaru btn-active" data-id="terbaru"><?php echo get_langArray('f_campaign_button3');?></button>
							<button class="terbesar" data-id="terbesar"><?php echo get_langArray('f_campaign_button4');?></button>
							<?php } ?>

							<?php
							$set_rand = d_randomString(4);
							$set_rand2 = d_randomString(5);
							?>
							<div class="donation_box black box_terbaru" style="background: #ffffff;">
							    <div id="box_<?php echo $set_rand; ?>">
							        <?php
							        foreach ($donasi as $value) {
							        	$readtime = new donasiyuk_readtime();
										$donation_time = $readtime->time_donation($value->created_at);

										$donatur_name = $value->name;
										$anonim = 'Orang Baik';
										if($value->anonim=='1'){
											$donatur_name = $anonim_text;
										}
										$donatur_name = wp_strip_all_tags($donatur_name);

							        	echo '
								        <div class="donation_inner_box" style="background:rgb(250, 252, 255);">
								            <div class="donation_name">'.$donatur_name.'<span class="donation_time"><span class="dashicons dashicons-clock"></span>'.$donation_time.'</span>
								            </div>
								            <div class="donation_total">Ber'.strtolower($allocation_title).' sebesar <b>'.$show_currency.number_format_currency($value->nominal).'</b></div>
								        </div>
								        ';
							        }
							        ?>
							    </div>
							    <div id="box_btn_<?php echo $set_rand; ?>" class="donation_button">
							        <?php if($donasi==null){ ?>
							    	<p style="text-align: center;"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/icons/givelove.png'; ?>" style="width: 120px;"></p>
							    	<p style="color: #a3aab7;font-weight: 400;">Belum ada donasi untuk penggalangan dana ini</p>
							    	<?php }else if(!empty($total_donasi) && (int)$total_donasi->jumlah > 5){ ?>
							        <div class="loadmore_info"></div>
							        <button id="<?php echo $set_rand; ?>" class="load_data_donatur" data-act="terbaru" data-id="<?php echo $row->campaign_id;?>" data-count="2" data-fullanonim="true" data-anonim="<?php echo $anonim_text; ?>">Load more</button>
								    <?php } ?>
							    </div>
							</div>


							<div class="donation_box black box_terbesar" style="background: #ffffff;display: none;">
							    <div id="box_<?php echo $set_rand2; ?>">
							        <?php
							        foreach ($donasi2 as $value) {
							        	$readtime = new donasiyuk_readtime();
										$donation_time = $readtime->time_donation($value->created_at);

										$donatur_name = $value->name;
										$anonim = 'Orang Baik';
										if($value->anonim=='1'){
											$donatur_name = $anonim_text;
										}
										$donatur_name = wp_strip_all_tags($donatur_name);

							        	echo '
								        <div class="donation_inner_box" style="background:rgb(250, 252, 255);">
								            <div class="donation_name">'.$donatur_name.'<span class="donation_time"><span class="dashicons dashicons-clock"></span>'.$donation_time.'</span>
								            </div>
								            <div class="donation_total">Ber'.strtolower($allocation_title).' sebesar <b>'.$show_currency.number_format_currency($value->nominal).'</b></div>
								        </div>
								        ';
							        }
							        ?>
							    </div>
							    <div id="box_btn_<?php echo $set_rand2; ?>" class="donation_button">
							        <?php if($donasi==null){ ?>
							    	<p style="text-align: center;"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/icons/givelove.png'; ?>" style="width: 120px;"></p>
							    	<p style="color: #a3aab7;font-weight: 400;">Belum ada donasi untuk penggalangan dana ini</p>
							    	<?php }else if(!empty($total_donasi) && (int)$total_donasi->jumlah > 5){ ?>
							        <div class="loadmore_info"></div>
							        <button id="<?php echo $set_rand2; ?>" class="load_data_donatur" data-act="terbesar" data-id="<?php echo $row->campaign_id;?>" data-count="2" data-fullanonim="true" data-anonim="<?php echo $anonim_text; ?>">Load more</button>
								    <?php } ?>
							    </div>
							</div>
							<!-- end donation -->
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>
	<?php }else{ ?>
	<div class="section-box"><h3>Keterangan</h3>

		<div id="keterangan-donasi" class="readmore-desc">
			<p><?php echo $information; ?></p>
			<?php if($readmore_description=='1' ){ ?>
			<p class="box-button-readmore">
		        <a class="button btn-readmore" href="#"><?php echo get_langArray('f_campaign_button1');?> ▾</a>
		    </p>
			<?php } ?>
		</div>

	</div>

	<?php
		if($fundraiser_on=='1'){
			if($row->fundraiser_setting=='0' || $row->fundraiser_setting==null){
				$fundraiser_show = true;
			}else if($row->fundraiser_setting=='1'){
				if($row->fundraiser_on=='1'){
					$fundraiser_show = true;
					if($row->fundraiser_button==''){
						$fundraiser_button = 'Jadi Fundraiser';
					}else{
						$fundraiser_button = $row->fundraiser_button;
					}
					$fundraiser_text = $row->fundraiser_text;
				}else{
					$fundraiser_show = false;
				}
			}else{
				$fundraiser_show = false;
			}
		}else{
			$fundraiser_show = false;
		}
	?>

	<?php
	// Data calculation for Campaign Navigation List Card
	$dyk_months = array(
	    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
	    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
	);

	// 1. Kabar Terbaru
	$dyk_count_kabar = !empty($campaign_update) && is_array($campaign_update) ? count($campaign_update) : 0;
	$dyk_last_update_str = '';
	if (!empty($campaign_update) && isset($campaign_update[0]->created_at)) {
	    $dt_temp = new DateTime($campaign_update[0]->created_at);
	    $dyk_last_update_str = $dt_temp->format('j') . ' ' . $dyk_months[(int)$dt_temp->format('n')] . ' ' . $dt_temp->format('Y');
	} elseif (!empty($row->created_at)) {
	    $dt_temp = new DateTime($row->created_at);
	    $dyk_last_update_str = $dt_temp->format('j') . ' ' . $dyk_months[(int)$dt_temp->format('n')] . ' ' . $dt_temp->format('Y');
	} else {
	    $dt_temp = new DateTime();
	    $dyk_last_update_str = $dt_temp->format('j') . ' ' . $dyk_months[(int)$dt_temp->format('n')] . ' ' . $dt_temp->format('Y');
	}

	// 2. Fundraiser
	$dyk_count_fundraiser = !empty($all_fundraiser) && is_array($all_fundraiser) ? count($all_fundraiser) : 0;

	// 3. Donasi
	$dyk_count_donasi = !empty($total_donasi) && isset($total_donasi->jumlah) ? (int)$total_donasi->jumlah : 0;

	$dt_pub = !empty($row->created_at) ? new DateTime($row->created_at) : new DateTime();
	$m_pub = $dt_pub->format('F');
	if (strpos($m_pub, 'January') !== false ) { $m_pub = 'Januari'; }
	elseif(strpos($m_pub, 'February') !== false ) { $m_pub = 'Februari'; }
	$dt_published = $m_pub.', '.$dt_pub->format('j Y');
	?>

	<!-- Modern Campaign Navigation Card (Accordion) -->
	<div class="section-box dyk-nav-section" style="padding:0;background:transparent;box-shadow:none;border:none;">
		<div class="dyk-nav-list-card">
			<!-- 1. Kabar Terbaru -->
			<div class="dyk-nav-item dyk-accordion-toggle" data-target="#dyk-collapse-kabar">
				<div class="dyk-nav-item-left">
					<div class="dyk-nav-item-header">
						<span class="dyk-nav-item-title">Kabar Terbaru</span>
						<?php if($dyk_count_kabar > 0){ ?>
							<span class="dyk-nav-badge"><?php echo number_format($dyk_count_kabar, 0, ',', '.'); ?></span>
						<?php } ?>
					</div>
					<div class="dyk-nav-item-subtitle">Terakhir update &bull; <?php echo esc_html($dyk_last_update_str); ?></div>
				</div>
				<div class="dyk-nav-item-arrow">
					<svg class="dyk-chevron" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
				</div>
			</div>
			<div id="dyk-collapse-kabar" class="dyk-nav-collapse-content" style="display:none;">
				<div class="dyk-collapse-inner">
					<div class="dyk-kabar-container">
						<?php if($action==true){ ?>
							<div style="text-align:right;margin-bottom:12px;">
								<a href="<?php echo admin_url('admin.php?page=donasiyuk_data_campaign&action=info_update&id=').$row->campaign_id ?>" style="display:inline-flex;align-items:center;gap:6px;background:#f0f9ff;color:#0284c7;border:1px solid #bae6fd;padding:6px 14px;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;">
									<span>+ Tambah Kabar Terbaru</span>
								</a>
							</div>
						<?php } ?>

						<?php if(empty($campaign_update)){ ?>
							<div style="text-align:center;padding:20px 10px;color:#64748b;">
								<div style="font-size:14px;font-weight:600;color:#334155;margin-bottom:4px;">Belum Ada Kabar Terbaru</div>
								<div style="font-size:12.5px;">Penggalang dana belum memposting update kabar terbaru.</div>
							</div>
						<?php } else { ?>
							<?php 
							$dyk_author_name = !empty($profile[0]->first_name) ? ($profile[0]->first_name . ' ' . $profile[0]->last_name) : (!empty($row->name) ? $row->name : 'Penggalang Dana');
							$dyk_author_name = wp_strip_all_tags($dyk_author_name);
							$dyk_author_photo = !empty($profile_photo) ? $profile_photo : plugin_dir_url( __FILE__ ) . 'assets/images/user.png';

							foreach ($campaign_update as $value) { 
								$readtime = new donasiyuk_readtime();
								$time_update = $readtime->time_donation($value->created_at);

								$dt = new DateTime($value->created_at);
								$m = $dt->format('F');
								if (strpos($m, 'January') !== false ) { $m = 'Januari'; }
								elseif(strpos($m, 'February') !== false ) { $m = 'Februari'; }
								$dt_up_str = $dt->format('j') . ' ' . $m . ' ' . $dt->format('Y');

								$information_update = str_replace("'", "&#39;", $value->information);
								$information_update = str_replace('../wp-content', get_site_url().'/wp-content', $information_update);
							?>
								<div class="dyk-kabar-item">
									<div class="dyk-kabar-author">
										<div class="dyk-kabar-avatar">
											<img src="<?php echo esc_url($dyk_author_photo); ?>" alt="<?php echo esc_attr($dyk_author_name); ?>">
										</div>
										<div class="dyk-kabar-author-info">
											<span class="dyk-kabar-author-name"><?php echo esc_html($dyk_author_name); ?></span>
											<span class="dyk-kabar-time"><?php echo esc_html($time_update); ?> &bull; <?php echo esc_html($dt_up_str); ?></span>
										</div>
									</div>

									<h4 class="dyk-kabar-title"><?php echo wp_strip_all_tags($value->title); ?></h4>

									<div class="dyk-kabar-content">
										<div class="dyk-kabar-body is-truncated">
											<?php echo $information_update; ?>
										</div>
										<a href="javascript:void(0);" class="dyk-kabar-readmore">Selengkapnya</a>
									</div>

									<?php if($action==true){ ?>
										<div style="text-align:right;margin-top:8px;">
											<a href="<?php echo admin_url('admin.php?page=donasiyuk_data_campaign&action=info_update&id=').$row->campaign_id.'&infoid='.$value->id ?>" style="display:inline-flex;align-items:center;gap:4px;color:#0284c7;font-size:12.5px;font-weight:600;text-decoration:none;">
												<img alt="" src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/pencil.png'; ?>" style="width:10px;"> Edit Info
											</a>
										</div>
									<?php } ?>
								</div>
							<?php } ?>
						<?php } ?>

						<!-- Published Milestone -->
						<div class="dyk-kabar-milestone">
							<div class="dyk-kabar-milestone-icon">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
							</div>
							<div class="dyk-kabar-milestone-info">
								<span class="dyk-kabar-milestone-title">Campaign dipublikasikan</span>
								<span class="dyk-kabar-milestone-date"><?php echo esc_html($dt_published); ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- 2. Fundraiser -->
			<div class="dyk-nav-item dyk-accordion-toggle" data-target="#dyk-collapse-fundraiser">
				<div class="dyk-nav-item-left">
					<div class="dyk-nav-item-header">
						<span class="dyk-nav-item-title">Fundraiser</span>
						<?php if($dyk_count_fundraiser > 0){ ?>
							<span class="dyk-nav-badge"><?php echo number_format($dyk_count_fundraiser, 0, ',', '.'); ?></span>
						<?php } ?>
					</div>
				</div>
				<div class="dyk-nav-item-arrow">
					<svg class="dyk-chevron" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
				</div>
			</div>
			<div id="dyk-collapse-fundraiser" class="dyk-nav-collapse-content" style="display:none;">
				<div class="dyk-collapse-inner">
					<?php
					$set_rand_f = d_randomString(4);
					?>
					<div class="donation_box black" style="background:#ffffff;box-shadow:none;border:none;margin:0;padding:0;">
						<?php if($data_fundraiser_settings=='1' && !empty($get_fundraiser)){ ?>
					    <div id="box_<?php echo $set_rand_f; ?>">
					        <?php
					        foreach ($get_fundraiser as $value) {
					        	$user_info = get_userdata($value->fundraiser_id);
							    $fundraiser_fullname = $user_info ? ($user_info->first_name.' '.$user_info->last_name) : 'Fundraiser';
							    $fundraiser_fullname = wp_strip_all_tags($fundraiser_fullname);

					        	echo '
						        <div class="donation_inner_box" style="background:rgb(250, 252, 255);line-height:1.6;margin-bottom:8px;">
						            <div class="donation_name" style="color:'.$button_color.'">'.$fundraiser_fullname.'</div>
						            <div class="donation_comment" style="margin:0;">Berhasil mengajak '.$value->jumlah_donatur.' orang untuk berdonasi.<br></div>
						            <div class="donation_name">'.$show_currency.'&nbsp;'.number_format_currency($value->total).'</div>
						        </div>
						        ';
					        }
					        ?>
					    </div>
					    <div id="box_btn_<?php echo $set_rand_f; ?>" class="donation_button" style="text-align:center;margin:10px 0;">
					    	<?php if(count($all_fundraiser) > 5){ ?>
					        <div class="loadmore_info"></div>
					        <button id="<?php echo $set_rand_f; ?>" class="load_fundraiser" data-id="<?php echo $row->campaign_id;?>" data-count="2" data-fullanonim="true" data-anonim="<?php echo $anonim_text; ?>">Load more</button>
						    <?php } ?>
					    </div>
						<?php } else { ?>
							<p style="text-align:center;color:#a3aab7;font-size:13.5px;margin:10px 0;">Belum ada Fundraiser</p>
						<?php } ?>

						<?php if($fundraiser_show==true){ ?>
						<div style="text-align: center; padding: 15px 10px; border-radius: 8px; margin-top: 10px; background: #f8fafc; border: 1px dashed #cbd5e1;">
						    <div style="font-size: 14px; margin-bottom: 8px; font-weight:600; color:#334155;"><?php echo !empty($fundraiser_text) ? $fundraiser_text : 'Ajak teman dan keluarga ikut berdonasi'; ?></div>
						    	<?php if(empty($aff_code)){ ?>
						    		<div><button class="donation_button_fundraiser regaff scale_button" data-cid="<?php echo $row->campaign_id; ?>" style="background:<?php echo $colornya; ?>;border-color:<?php echo $button_color;?>;padding:8px 16px;border-radius:8px;cursor:pointer;"><div><img alt="Image" src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/groups.png'; ?>"><div class="text-fundraiser" style="color:#2D3849;font-weight:700;"><?php echo !empty($fundraiser_button) ? $fundraiser_button : 'Jadi Fundraiser'; ?><div class="fundraiser-loading fundraiser-hide"></div></div></div></button></div>
							    <?php }else{ ?>
							    	<div><button class="donation_button_fundraiser copy_link_aff scale_button" data-cid="<?php echo $row->campaign_id; ?>" style="background:<?php echo $colornya; ?>;border-color:<?php echo $button_color;?>;padding:8px 16px;border-radius:8px;cursor:pointer;" data-link="<?php echo $current_url; ?><?php if(!empty($aff_code)){echo '?ref='.$aff_code;}?>"><div><img alt="Image" src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/link2.png'; ?>"><div class="text-fundraiser" style="color:#2D3849;font-weight:700;">Copy Link Aff</div></div></button></div>
							    <?php } ?>
						    </div>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>

			<!-- 3. Donasi -->
			<div class="dyk-nav-item dyk-accordion-toggle" data-target="#dyk-collapse-donasi">
				<div class="dyk-nav-item-left">
					<div class="dyk-nav-item-header">
						<span class="dyk-nav-item-title">Donasi</span>
						<?php if($dyk_count_donasi > 0){ ?>
							<span class="dyk-nav-badge"><?php echo number_format($dyk_count_donasi, 0, ',', '.'); ?></span>
						<?php } ?>
					</div>
				</div>
				<div class="dyk-nav-item-arrow">
					<svg class="dyk-chevron" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
				</div>
			</div>
			<div id="dyk-collapse-donasi" class="dyk-nav-collapse-content" style="display:none;">
				<div class="dyk-collapse-inner">
					<?php if(!empty($donasi)){ ?>
					<div style="margin-bottom:12px;display:flex;gap:8px;">
						<button class="terbaru btn-active" data-id="terbaru"><?php echo get_langArray('f_campaign_button3');?></button>
						<button class="terbesar" data-id="terbesar"><?php echo get_langArray('f_campaign_button4');?></button>
					</div>
					<?php } ?>

					<?php
					$set_rand_d1 = d_randomString(4);
					$set_rand_d2 = d_randomString(5);
					?>
					<div class="donation_box black box_terbaru" style="background: #ffffff;box-shadow:none;border:none;margin:0;padding:0;">
					    <div id="box_<?php echo $set_rand_d1; ?>">
					        <?php
					        if(!empty($donasi)){
						        foreach ($donasi as $value) {
						        	$readtime = new donasiyuk_readtime();
									$donation_time = $readtime->time_donation($value->created_at);

									$donatur_name = $value->name;
									if($value->anonim=='1'){
										$donatur_name = !empty($anonim_text) ? $anonim_text : 'Orang Baik';
									}
									$donatur_name = wp_strip_all_tags($donatur_name);

						        	echo '
							        <div class="donation_inner_box" style="background:rgb(250, 252, 255);margin-bottom:8px;">
							            <div class="donation_name">'.$donatur_name.'<span class="donation_time"><span class="dashicons dashicons-clock"></span>'.$donation_time.'</span>
							            </div>
							            <div class="donation_total">Ber'.strtolower($allocation_title).' sebesar <b>'.$show_currency.number_format_currency($value->nominal).'</b></div>
							        </div>
							        ';
						        }
					    	}
					        ?>
					    </div>
					    <div id="box_btn_<?php echo $set_rand_d1; ?>" class="donation_button" style="text-align:center;margin-top:10px;">
					        <?php if(empty($donasi)){ ?>
					    	<p style="text-align: center;"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/icons/givelove.png'; ?>" style="width: 80px;"></p>
					    	<p style="color: #a3aab7;font-weight: 400;font-size:13.5px;">Belum ada donasi untuk penggalangan dana ini</p>
					    	<?php }else if(!empty($total_donasi) && (int)$total_donasi->jumlah > 5){ ?>
					        <div class="loadmore_info"></div>
					        <button id="<?php echo $set_rand_d1; ?>" class="load_data_donatur" data-act="terbaru" data-id="<?php echo $row->campaign_id;?>" data-count="2" data-fullanonim="true" data-anonim="<?php echo $anonim_text; ?>">Load more</button>
						    <?php } ?>
					    </div>
					</div>

					<div class="donation_box black box_terbesar" style="background: #ffffff;box-shadow:none;border:none;margin:0;padding:0;display: none;">
					    <div id="box_<?php echo $set_rand_d2; ?>">
					        <?php
					        if(!empty($donasi2)){
						        foreach ($donasi2 as $value) {
						        	$readtime = new donasiyuk_readtime();
									$donation_time = $readtime->time_donation($value->created_at);

									$donatur_name = $value->name;
									if($value->anonim=='1'){
										$donatur_name = !empty($anonim_text) ? $anonim_text : 'Orang Baik';
									}
									$donatur_name = wp_strip_all_tags($donatur_name);

						        	echo '
							        <div class="donation_inner_box" style="background:rgb(250, 252, 255);margin-bottom:8px;">
							            <div class="donation_name">'.$donatur_name.'<span class="donation_time"><span class="dashicons dashicons-clock"></span>'.$donation_time.'</span>
							            </div>
							            <div class="donation_total">Ber'.strtolower($allocation_title).' sebesar <b>'.$show_currency.number_format_currency($value->nominal).'</b></div>
							        </div>
							        ';
						        }
						    }
					        ?>
					    </div>
					    <div id="box_btn_<?php echo $set_rand_d2; ?>" class="donation_button" style="text-align:center;margin-top:10px;">
					        <?php if(empty($donasi2)){ ?>
					    	<p style="text-align: center;"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/icons/givelove.png'; ?>" style="width: 80px;"></p>
					    	<p style="color: #a3aab7;font-weight: 400;font-size:13.5px;">Belum ada donasi untuk penggalangan dana ini</p>
					    	<?php }else if(!empty($total_donasi) && (int)$total_donasi->jumlah > 5){ ?>
					        <div class="loadmore_info"></div>
					        <button id="<?php echo $set_rand_d2; ?>" class="load_data_donatur" data-act="terbesar" data-id="<?php echo $row->campaign_id;?>" data-count="2" data-fullanonim="true" data-anonim="<?php echo $anonim_text; ?>">Load more</button>
						    <?php } ?>
					    </div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php } ?>

	<div class="section-box dyk-doa-section">
		<div class="dyk-doa-container">
			<div class="dyk-doa-header">
				<div class="dyk-doa-title-wrapper">
					<h3 class="dyk-doa-title">Doa-doa Orang Baik</h3>
					<?php if(!empty($data_comment) && (int)$data_comment->jumlah > 0){ ?>
						<span class="dyk-doa-count-badge"><?php echo number_format((int)$data_comment->jumlah, 0, ',', '.'); ?></span>
					<?php } ?>
				</div>
				<div class="dyk-doa-arrow" title="Lihat semua doa">
					<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
				</div>
			</div>

			<?php
			$set_rand = d_randomString(4);
			?>
			<div id="box_<?php echo $set_rand; ?>" class="dyk-doa-list">
				<?php
				if(!empty($donasi_comment)){
					foreach ($donasi_comment as $value) {
						$readtime = new donasiyuk_readtime();
						$donation_time = $readtime->time_donation($value->created_at);

						$donatur_name = $value->name;
						if($value->anonim == '1'){
							$donatur_name = !empty($anonim_text) ? $anonim_text : 'Orang Baik';
						}
						$donatur_name = wp_strip_all_tags($donatur_name);

						// check if user has amined / loved
						$a = donasiyuk_getIP();
						$b = donasiyuk_getOS();
						$c = donasiyuk_getBrowser();
						$d = donasiyuk_getMobDesktop();
						$data = $wpdb->get_results('SELECT * from '.$table_name4.' where ip="'.$a.'" and os="'.$b.'" and browser="'.$c.'" and mobdesktop="'.$d.'" and donate_id="'.$value->id.'"');
						$is_loved = !empty($data);

						$total_love = $wpdb->get_results("SELECT SUM(love) as jumlah FROM $table_name4 where donate_id='$value->id' ")[0];
						$love_count = !empty($total_love->jumlah) ? (int)$total_love->jumlah : 0;
						$heart_color = $is_loved ? '#ef4444' : '#6b7280';
						$comment = wp_strip_all_tags(str_replace('\\', '', $value->comment));
						$clean_comment_json = htmlspecialchars(json_encode($comment), ENT_QUOTES, 'UTF-8');
				?>
						<div class="dyk-doa-card" id="doa_card_<?php echo $value->id; ?>">
							<div class="dyk-doa-card-header">
								<div class="dyk-doa-user">
									<div class="dyk-doa-avatar">
										<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="#9ca3af"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
									</div>
									<div class="dyk-doa-user-meta">
										<div class="dyk-doa-name"><?php echo esc_html($donatur_name); ?></div>
										<div class="dyk-doa-time"><?php echo esc_html($donation_time); ?></div>
									</div>
								</div>
								<div class="dyk-doa-more" onclick="dykShareDoa(this, <?php echo $clean_comment_json; ?>);" title="Opsi">
									<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
								</div>
							</div>
							<div class="dyk-doa-text"><?php echo nl2br(esc_html($comment)); ?></div>
							<div class="dyk-doa-stats" id="stats_love_<?php echo $value->id; ?>">
								<strong><?php echo number_format($love_count, 0, ',', '.'); ?> orang</strong> mengaminkan doa ini
							</div>
							<div class="dyk-doa-divider"></div>
							<div class="dyk-doa-actions">
								<button type="button" class="dyk-doa-btn dyk-btn-amin donation_love <?php echo $is_loved ? 'active loved' : ''; ?>" id="love_<?php echo $value->id; ?>" data-donateid="<?php echo $value->id; ?>" data-campaignid="<?php echo $value->campaign_id; ?>" data-count="<?php echo $love_count; ?>" title="Aamiin-kan doa ini">
									<svg class="dyk-heart-icon" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="<?php echo $heart_color; ?>" stroke="<?php echo $heart_color; ?>" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
									<span>Aamiin</span>
									<div class="plus1">+1</div>
								</button>
								<button type="button" class="dyk-doa-btn dyk-btn-share" onclick="dykShareDoa(this, <?php echo $clean_comment_json; ?>);" title="Bagikan doa">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
									<span>Bagikan</span>
								</button>
							</div>
						</div>
				<?php
					}
				}
				?>
			</div>
			
			<div id="box_btn_<?php echo $set_rand; ?>" class="dyk-doa-loadmore-box">
				<?php if(empty($donasi_comment)){ ?>
					<div class="dyk-doa-empty">
						<p style="text-align: center;"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/icons/giving.png'; ?>" style="width: 80px; margin-bottom: 8px;"></p>
						<p style="color: #9ca3af; font-size: 13.5px;">Menanti doa-doa orang baik</p>
					</div>
				<?php } else if(!empty($data_comment) && (int)$data_comment->jumlah > 5){ ?>
					<div class="loadmore_info" style="display:none; color:#64748b; font-size:12px; margin-bottom:6px;"></div>
					<button id="<?php echo $set_rand; ?>" class="load_doa_donatur dyk-doa-loadmore-btn" data-id="<?php echo $row->campaign_id;?>" data-count="2" data-fullanonim="true" data-anonim="<?php echo $anonim_text; ?>">Load more</button>
				<?php } ?>
			</div>
		</div>
	</div>

	<div class="section-box box-powered">
		<?php if($powered_by_setting=='1'){ ?>
		<div class="powered-donasiyuk-box"><a href="https://donasiyuk.id" target="_blank">Powered by DonasiYuk</a></div>
		<?php } ?>
	</div>
	
	<div id="lala-alert-container"><div id="lala-alert-wrapper"></div></div>
	<div class="section-box" id="fixed-button">
		<button type="button" class="dyk-sticky-btn-share dyk-open-share" title="Bagikan Campaign">
			<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
			<span>Bagikan</span>
		</button>
		<?php if($hasil->invert==true){ ?><a href="javascript:;" class="button-disabled"><button class="donation_button_now2"><?php echo $text1; ?></button></a><?php }elseif($row->publish_status=='3'){ ?><a href="javascript:;" class="button-disabled"><button class="donation_button_now2"><?php echo $text1; ?></button></a><?php } elseif($donasi_terpenuhi==true){ ?><a href="javascript:;" class="button-disabled"><button class="donation_button_now2"><?php echo $text2; ?> Terpenuhi</button></a><?php }else{ ?>
		
		<?php if (!empty($row->external_link_button)) { ?>

			<a href="<?php echo $external_link;?>" target="_parent"><button class="donation_button_now2 scale_button" style="background:<?php echo $button_color;?>;border-color:<?php echo $button_color;?>"><?php if($option_baznasjabar_link=='1'){ ?><img alt="logo" src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/garuda.png'; ?>" style="width:27px;position: absolute;margin-top:-4px;margin-left: -10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php } ?><?php if($option_whatsapp_link=='1'){ ?><img alt="logo" src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/whatsapp_icon.png'; ?>" style="width:22px;position: absolute;margin-top:-2px;margin-left: -10px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php } ?><?php echo $text1; ?></button></a>

		<?php }else{ ?>

			<a href="<?php echo $current_url;?>/<?php echo $page_donate; ?><?php echo $link_ref_aff; ?>"><button class="donation_button_now2 scale_button" style="background:<?php echo $button_color;?>;border-color:<?php echo $button_color;?>"><?php echo $text1; ?></button></a>

		<?php } ?>

		

		<?php }?>
	</div>

	<div id="fixed-share-button" class="section-box">
		<div class="share-title">Bagikan melalui:</div>
		<div class="share-close">✕ Close</div>

		<button class="donation_social_button donasiyuk_copy_link" data-link="<?php echo $current_url; ?><?php if($aff_code!=''){echo'?ref='.$aff_code;}?>"><span><img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/link.png'; ?>" style="opacity: 0;margin-left: -15px;" alt="Copy Link"><div class="text-copy">Copy Link</div></span></button>

		<a class="donasiyuk-share wa" href="https://api.whatsapp.com/send?&text=<?php echo $row->title; ?>%0A<?php echo $current_url; ?><?php if($aff_code!=''){echo'?ref='.$aff_code;}?>">
			<button class="donation_social_button whatsaap"><span><img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/whatsapp.png'; ?>" alt="Whatsaap"></span>
			</button>
		</a>

		<a class="donasiyuk-share fb" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $current_url; ?><?php if($aff_code!=''){echo'?ref='.$aff_code;}?>">
			<button class="donation_social_button facebook"><span><img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/facebook.png'; ?>" alt="Facebook"></span>
			</button>
		</a>

		<a class="donasiyuk-share twit" href="https://twitter.com/intent/tweet?text=<?php echo $current_url; ?><?php if($aff_code!=''){echo'?ref='.$aff_code;}?>">
			<button class="donation_social_button twitter"><span><img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/x.png'; ?>" alt="Twitter"></span>
			</button>
		</a>

		<a class="donasiyuk-share tele" href="https://telegram.me/share/url?text=<?php echo $row->title; ?> &url=<?php echo $current_url; ?><?php if($aff_code!=''){echo'?ref='.$aff_code;}?>">
			<button class="donation_social_button telegram"><span><img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/telegram.png'; ?>" alt="Telegram"></span>
			</button>
		</a>
	</div>
   	


	    <?php if($flying_button_settings=='1' and $page_campaign_button=='1'){ ?>
	        <?php $wa_admin = wa_variants_08_628_2($flying_button_number); ?>
	        <!-- Floating Button - Whatsapp CS -->
	        <a href="https://api.whatsapp.com/send?phone=<?php echo $wa_admin; ?>&text=<?php echo urlencode($flying_button_message); ?>" 
	           class="whatsapp-float" target="_blank" style="cursor: pointer;">
	           <?php if($flying_button_bubble_text!=''){ ?><div class="chat-bubble"><?php echo $flying_button_bubble_text; ?></div><?php } ?>
	           <img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/icons/whatsapp.svg'; ?>" class="whatsapp-icon" alt="" />
	        </a>
	    <?php } ?>



	<?php if($row->socialproof=='1') { 
	$decoding = "decoding='async'";
	echo '<script src="'.plugin_dir_url( __FILE__ ).'assets/js/toastify.js"></script>';

	echo '
		<script>

		Array.prototype.getRandomColor= function(cut){
		    var i= Math.floor(Math.random()*this.length);
		    if(cut && i in this){
		        return this.splice(i, 1)[0];
		    }
		    return this[i];
		}
		var avatar_colors= ["#D94452", "#FA6C51", "#F5B945", "#9ED26A", "#35BA9B", "#4FC0E8", 
		"#9579DA", "#E3B692", "#E5C3C1", "#E7CDAC"];
		splittedContent = [
				'.$data_donasinya.'
	        ];

		function loopSplit(splittedContent) {
		    for (var i = 0; i < splittedContent.length; i++) {
		        (function (i) {
		        })(i);
		    };
		}
		loopSplit(splittedContent);
				
		var d = 0, howManyTimes = splittedContent.length;

		function f_socialproof() {

		  	var name 	= splittedContent[d].content[0];
		    var time 	= " - "+splittedContent[d].content[1];
		    var title 	= splittedContent[d].content[2];
		    var pp_url 	= splittedContent[d].content[3];
		    var c_id 	= splittedContent[d].content[4];
		    show_color_avatar = "";
		    if(pp_url!=""){
		    	show_color_avatar = "display:none;";
		    }
		    show_img_avatar = "";
		    if(pp_url==""){
		    	show_img_avatar = "display:none;";
		    }
		    var show_time = "'.$time.'";
		    if(show_time=="hide"){
		    	time = "";
		    }
		    setTimeout(function() {
		       Toastify({
				  text: "<div class=dsproof-container id="+c_id+"><div class=dsproof-avatar style=background:"+avatar_colors.getRandomColor()+";"+show_color_avatar+">"+name.substring(0, 1)+"</div><div class=dsproof-avatar style="+show_img_avatar+"><img '.$decoding.' src="+pp_url+"></div><div class=dsproof-content><div class=dsproof-name>"+name+"</div><div class=dsproof-title>"+title+"</div><div class=dsproof-verified><img src='.plugin_dir_url( __FILE__ ).'assets/images/check.png'.'><span>Verified"+time+"</span></div><div></div>",
				  className: "donasiyuk-socialproof'.$set_style.'",
				  escapeMarkup : false,
				  gravity: "'.$p_gravity.'",
				  position: "'.$p_position.'",
				  close: "'.$close.'",
				  duration: 5000,
				  style: {
				    background: "linear-gradient(to right, #ffffff, #ffffff)",
				  }
				}).showToast();
			}, 1000)
		  d++;
		  if (d < howManyTimes) {
		    setTimeout(f_socialproof, '.$delay.');
		  }
		}
		if(splittedContent.length>=1){
			f_socialproof();   
		}
		</script>

	';

	echo '<style>
		.donasiyuk-socialproof{line-height: 1.5;border-radius:6px;max-width:360px;height:auto;padding-right:20px!important;z-index:9999;background:#fff!important;box-shadow: 0 3px 6px -1px rgba(0, 0, 0, 0.06),0 10px 36px -4px rgba(77, 96, 232, 0.09) !important;}.donasiyuk-socialproof .toast-close{border-radius:20px;position:absolute;right:0;color:#fff;margin-top:-16px!important;background:#0000004f;width:25px!important;height:25px!important;font-size:13px!important;text-align:center!important;padding:2px!important;opacity:1;top:10px}.dsproof-avatar{border-radius:4px;width:50px;height:50px;text-align:center;position:absolute;margin-left:-7px;margin-top:0px;font-size:32px;font-weight:700;color:#fffc;font-family:Lato,lato,sans-serif!important}.dsproof-avatar img{width:50px;height:50px;border-radius:4px;}.dsproof-content{margin-left:54px;color:#888;font-size:11px;font-family:Lato,lato,sans-serif!important}.dsproof-name{font-size:13px;font-weight:700;color:#35363c;position:absolute;margin-top:-3px}.dsproof-title{color:#656577;padding-top:16px;padding-bottom:2px}.dsproof-verified{font-size:10px;color:#b0b0c6;margin-bottom:2px;}.dsproof-verified span{padding-left:15px}.dsproof-verified img{width:12px;position:absolute;margin-top:2px}.toastify{min-width:160px;padding:12px 20px;padding-top:12px!important;color:#fff;display:inline-block;box-shadow:0 3px 6px -1px rgba(0,0,0,.12),0 10px 36px -4px rgba(77,96,232,.3);background:-webkit-linear-gradient(315deg,#73a5ff,#5477f5);background:linear-gradient(135deg,#73a5ff,#5477f5);position:fixed;opacity:0;transition:all .4s cubic-bezier(.215,.61,.355,1);cursor:pointer;text-decoration:none;z-index:2147483647}.toastify.on{opacity:1}.toast-close{opacity:.4;padding:0 5px}.toastify-right{right:15px}.toastify-left{left:15px}.toastify-top{top:-150px}.toastify-bottom{bottom:-150px}.toastify-rounded{}.toastify-avatar{width:1.5em;height:1.5em;margin:-7px 5px;border-radius:2px}.toastify-center{margin-left:auto;margin-right:auto;left:0;right:0;max-width:fit-content;max-width:-moz-fit-content}@media only screen and (max-width:360px){.toastify-left,.toastify-right{margin-left:auto;margin-right:auto;left:0;right:0;max-width:fit-content}} .donasiyuk-socialproof.s-rounded .dsproof-avatar{border-radius: 50px;} .donasiyuk-socialproof.s-rounded .dsproof-avatar img{border-radius: 50px;}
		.donasiyuk-socialproof.s-rounded {height:auto !important;}
		.donasiyuk-socialproof.s-rounded .dsproof-avatar {margin-top:0px;}
		.donasiyuk-socialproof.s-flying { background: transparent !important;box-shadow:none !important;}
		.donasiyuk-socialproof.s-flying .dsproof-avatar { box-shadow: 0 3px 6px -1px rgba(0, 0, 0, 0.06),0 10px 36px -4px rgba(77, 96, 232, 0.04) !important;}
		.donasiyuk-socialproof.s-flying .dsproof-content { background: #fff;padding: 10px 20px 10px 16px;border-radius: 4px;box-shadow: 0 3px 6px -1px rgba(0, 0, 0, 0.06),0 10px 36px -4px rgba(77, 96, 232, 0.04)}
		@media only screen and (max-width:480px) {
		    .toastify-bottom {
		      bottom: 100px !important;
		    }
		}
	</style>';

	} // close socialproof ?>
	
	<script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/jquery.min.js';?>"></script>
	<script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/donasiyuk.min.js?ver='.$GLOBALS['donasiyuk_vars']['plugin_version'].'';?>"></script>
	<script>
		jQuery(document).ready(function($){
			$(window).scroll(function() {
			    var value_mgtop = $('.parallax-wrapper img.parallax').attr("data-mgtop");
			    $('.parallax-wrapper img.parallax').css({"margin-top":value_mgtop});
			});
		});
	</script>
	<script>
		const image = document.querySelector('.parallax');
		new Ukiyo(image, {
		    scale: 1.5, // 1~2 is recommended
		    speed: 1.5, // 1~2 is recommended
		    willChange: true, // This may not be valid in all cases
		    wrapperClass: "parallax-wrapper"
		})

		window.addEventListener("load", function() {

			// store tabs variable
			var myTabs = document.querySelectorAll("ul.nav-tabs > li");

			function myTabClicks(tabClickEvent) {

				for (var i = 0; i < myTabs.length; i++) {
					myTabs[i].classList.remove("active");
				}

				var clickedTab = tabClickEvent.currentTarget; 

				clickedTab.classList.add("active");

				tabClickEvent.preventDefault();

				var myContentPanes = document.querySelectorAll(".tab-pane");

				for (i = 0; i < myContentPanes.length; i++) {
					myContentPanes[i].classList.remove("active");
				}

				var anchorReference = tabClickEvent.target;
				var activePaneId = anchorReference.getAttribute("href");
				var activePane = document.querySelector(activePaneId);

				activePane.classList.add("active");

			}

			for (i = 0; i < myTabs.length; i++) {
				myTabs[i].addEventListener("click", myTabClicks)
			}
		});

		$(document).ready(function() {
		  $timelineExpandableTitle = $('.timeline-action.is-expandable .title');
		  
		  $($timelineExpandableTitle).attr('tabindex', '0');
		  
		  // Give timelines ID's
		  $('.timeline').each(function(i, $timeline) {
		    var $timelineActions = $($timeline).find('.timeline-action.is-expandable');
		    
		    $($timelineActions).each(function(j, $timelineAction) {
		      var $milestoneContent = $($timelineAction).find('.content');
		      
		      $($milestoneContent).attr('id', 'timeline-' + i + '-milestone-content-' + j).attr('role', 'region');
		      $($milestoneContent).attr('aria-expanded', $($timelineAction).hasClass('expanded'));
		      
		      $($timelineAction).find('.title').attr('aria-controls', 'timeline-' + i + '-milestone-content-' + j);
		    });
		  });
		  
		  $($timelineExpandableTitle).click(function() {
		    $(this).parent().toggleClass('is-expanded');
		    $(this).siblings('.content').attr('aria-expanded', $(this).parent().hasClass('is-expanded'));
		  });
		  
		  // Expand or navigate back and forth between sections
		  $($timelineExpandableTitle).keyup(function(e) {
		    if (e.which == 13){ //Enter key pressed
		      $(this).click();
		    } else if (e.which == 37 ||e.which == 38) { // Left or Up
		      $(this).closest('.timeline-milestone').prev('.timeline-milestone').find('.timeline-action .title').focus();
		    } else if (e.which == 39 ||e.which == 40) { // Right or Down
		      $(this).closest('.timeline-milestone').next('.timeline-milestone').find('.timeline-action .title').focus();
		    }
		  });
		});                  


		$(document).ready(function() {
		    $('.donasiyuk-share').click(function(e) {
		        e.preventDefault();
		        if ($(this).hasClass("wa") || $(this).hasClass("fb") || $(this).hasClass("twit") || $(this).hasClass("tele")) {
					window.open($(this).attr('href'), 'fbShareWindow', 'height=450, width=550, top=' + ($(window).height() / 2 - 275) + ', left=' + ($(window).width() / 2 - 225) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
						return false;
				}
		        
		    });

		    $('.terbaru, .terbesar').click(function() {
		        // Remove "btn-active" class from both buttons
		        $('.terbaru, .terbesar').removeClass('btn-active');

		        // Add "btn-active" class to the clicked button
		        $(this).addClass('btn-active');

		        var id =  $(this).attr('data-id');
		        if(id=='terbaru'){
		        	$('.box_terbaru').slideDown();
		        	$('.box_terbesar').slideUp();
		        }else{
		        	$('.box_terbaru').slideUp();
		        	$('.box_terbesar').slideDown();
		        }
		    });
		});

		$('.donation_button_share').bind("click", function(e) {
			$('#fixed-share-button').addClass("show-button");
		});
		$('.share-close').bind("click", function(e) {
			$('#fixed-share-button').removeClass("show-button");
		});

		<?php if($readmore_description=='1' ){ ?>

			$(".readmore-desc").readMore({
			    expandTrigger: ".box-button-readmore",
			    previewHeight: 400,
			    fadeColor1: "rgba(255,255,255,0)",
			    fadeColor2: "rgba(255,255,255,1)"
			});

		<?php } ?>

		$(function() {
		    var header = $("#header-title");
		    var header2 = $('.campaign-header-title');
		    var footer = $("#fixed-button");
		    $(window).scroll(function() {
		        var scroll = $(window).scrollTop();
			    var windowHeight = $(window).height();
			    var documentHeight = $(document).height();
			    var windowWidth = $(window).width(); // lebar layar

		        if (scroll >= 150) {
		            header.addClass("flying-header");
		            header2.addClass("show-title");
		        } else {
		            header.removeClass("flying-header");
		            header2.removeClass("show-title");
		        }

		        if (scroll >= 280) {
		            footer.addClass("flying-button");
		        } else {
		            footer.removeClass("flying-button");
		            $('#fixed-share-button').removeClass("show-button");
		        }

		        // Cek sisa scroll ke bawah
			    const distanceToBottom = documentHeight - (scroll + windowHeight);

			    // ubah ke persentase (0–100)
			    const percentFromBottom = (distanceToBottom / documentHeight) * 100;

			    // kalau sisa kurang dari 50% dari tinggi dokumen dan layar ≤480px
			    if (percentFromBottom <= 50 && windowWidth <= 480) {
			        $('.whatsapp-float').addClass("geser-dikit");
			    } else {
			        $('.whatsapp-float').removeClass("geser-dikit");
			    }

		    });
		});


		$(document).on("click", ".donasiyuk_copy_link", function(e) {
			var link_donasi = $(this).data("link");
			copyToClipboard(link_donasi);
			var message = "Copy link donasi berhasil!";
			var status = "success";    /* There are 4 statuses: success, info, warning, danger  */
			var timeout = 3000;     /* 5000 here means the alert message disappears after 5 seconds. */
			createAlert(message, status, timeout);
		});

		$(document).on("click", ".copy_link_aff", function(e) {
			var link_donasi = $(this).data("link");
			copyToClipboard(link_donasi);
			var message = "Copy Link Aff berhasil!";
			var status = "success";    /* There are 4 statuses: success, info, warning, danger  */
			var timeout = 3000;     /* 5000 here means the alert message disappears after 5 seconds. */
			createAlert(message, status, timeout);
		});

		$(document).on("click", ".regaff", function(e) {
			$('.fundraiser-loading').removeClass('fundraiser-hide');
			var cid = $(this).data("cid");
			var data_nya = [cid];
		    var data = {
		        "action": "dykfunction_regaff_fundraiser",
		        "datanya": data_nya
		    };

		    jQuery.post("<?php echo $home_url; ?>/wp-admin/admin-ajax.php", data, function(response) {

		    	var response_text = response.split("_");
                response_info = response_text[0];
                response_affcode = response_text[1];

                if(response_info=='loginfirst'){
			    	$('.fundraiser-loading').addClass('fundraiser-hide');

			    	var message = "Silahkan anda login terlebih dahulu.";
					var status = "warning";    /* There are 4 statuses: success, info, warning, danger  */
					var timeout = 3000;     /* 5000 here means the alert message disappears after 5 seconds. */
					createAlert(message, status, timeout);

					<?php if($login_setting=='1'){
					echo '
					setTimeout(function() {
			            var urlnya = "'.get_site_url().'/'.$page_login.'";
						window.location.replace(urlnya);
			        }, 1200)
					';
					}else{
					echo '
					setTimeout(function() {
			            var urlnya = "'.get_site_url().'/wp-login.php";
						window.location.replace(urlnya);
			        }, 1200)
					';
					} ?>

                }else{
                	var aff_url = "<?php echo $current_url; ?>"+'?ref='+response_affcode;
			    	$('.donation_button_fundraiser img').attr("src","<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/link2.png'; ?>");
			    	$('.donation_button_fundraiser').removeClass('regaff');
			    	$('.donation_button_fundraiser').addClass('copy_link_aff');
			    	$('.donation_button_fundraiser').attr('data-link', aff_url);
			    	$('.donation_button_fundraiser .text-fundraiser').text('Copy Link Aff');
			    	
			    	$('.fundraiser-loading').addClass('fundraiser-hide');

			    	copyToClipboard(aff_url);

			    	var message = "Link Aff Fundraiser berhasil didaftarkan dan di-copy. Silahkan sebarkan ke Social Media anda.";
					var status = "success";    /* There are 4 statuses: success, info, warning, danger  */
					var timeout = 3000;     /* 5000 here means the alert message disappears after 5 seconds. */
					createAlert(message, status, timeout);
                }

		    	
		    });
		});

		

		// get Copy
		function copyToClipboard(string) {
		let textarea;let result;try{textarea=document.createElement("textarea");textarea.setAttribute("readonly",!0);textarea.setAttribute("contenteditable",!0);textarea.style.position="fixed";textarea.value=string;document.body.appendChild(textarea);textarea.focus();textarea.select();const range=document.createRange();range.selectNodeContents(textarea);const sel=window.getSelection();sel.removeAllRanges();sel.addRange(range);textarea.setSelectionRange(0,textarea.value.length);result=document.execCommand("copy")}catch(err){console.error(err);result=null}finally{document.body.removeChild(textarea)}
	if(!result){const isMac=navigator.platform.toUpperCase().indexOf("MAC")>=0;const copyHotkey=isMac?"⌘C":"CTRL+C";result=box-button-readmore(`Press ${copyHotkey}`,string);if(!result){return!1}}
	return!0
		}

		function getNum(val) {
		   if (isNaN(val)) {
		     return 0;
		   }
		   return val;
		}

		$(function(){
		  $(document).on("click", ".donation_love", function(e) {
		    $(this).bind('animationend webkitAnimationEnd MSAnimationEnd oAnimationEnd', function(){
		        $(this).removeClass('active');
		    })
		     $(this).addClass("active");
		  });
		});


		// Doa Aamiin Click Handler
		$(document).on("click", ".donation_love", function(e) {
			e.preventDefault();
			var $btn = $(this);
			var id = $btn.attr('id');
			var campaign_id = $btn.attr('data-campaignid');
			var donate_id = $btn.attr('data-donateid');
			var $stats = $('#stats_' + id);
			var $heart = $btn.find('.dyk-heart-icon');
			
			var count = parseInt($btn.attr('data-count') || 0);
			if(isNaN(count)) count = 0;
			count += 1;
			$btn.attr('data-count', count);
			$btn.addClass('active loved');
			$heart.attr('fill', '#ef4444').attr('stroke', '#ef4444').css({'fill': '#ef4444', 'stroke': '#ef4444'});
			
			if($stats.length){
				$stats.html('<strong>' + count + ' orang</strong> mengaminkan doa ini');
			}
			
			$btn.find('.plus1').addClass('show').animate({
				top: '-24px',
				opacity: '0',
			}, {
				duration : 400, 
				complete : function(){
					$(this).removeClass('show').removeAttr('style');
				}
			});

			var data_nya = [campaign_id, donate_id];
		    var data = {
		        "action": "dykfunction_set_love",
		        "datanya": data_nya
		    };

		    jQuery.post("<?php echo $home_url; ?>/wp-admin/admin-ajax.php", data, function(response) {
		    	<?php if($max_love!='0'){?>
		    	if(response=='cukup'){
		    		alert('Maaf, hanya boleh <?php echo $max_love; ?> kali');
				}
				<?php } ?>
		    });
		});

		// Doa Share & Toast Handlers
		window.dykShareDoa = function(el, doaText) {
			var shareUrl = window.location.href;
			var shareTitle = document.title || 'Doa untuk Campaign';
			var shareText = doaText ? '"' + doaText + '" - Mari dukung dan aamiinkan doa ini bersama di DonasiYuk' : shareTitle;
			
			if (navigator.share) {
				navigator.share({
					title: shareTitle,
					text: shareText,
					url: shareUrl
				}).catch(function(err){});
			} else {
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(shareUrl).then(function() {
						showDykToast('Link campaign berhasil disalin!');
					}).catch(function() {
						prompt('Salin link doa berikut:', shareUrl);
					});
				} else {
					prompt('Salin link doa berikut:', shareUrl);
				}
			}
		};

		window.showDykToast = function(msg) {
			$('.dyk-toast').remove();
			var $toast = $('<div class="dyk-toast">' + msg + '</div>');
			$('body').append($toast);
			setTimeout(function() { $toast.addClass('show'); }, 10);
			setTimeout(function() {
				$toast.removeClass('show');
				setTimeout(function() { $toast.remove(); }, 300);
			}, 2500);
		};

		// Navigation Card Accordion Handler (Expand/Collapse in-place)
		$(document).on("click", ".dyk-accordion-toggle", function(e) {
			e.preventDefault();
			var $this = $(this);
			var target = $this.attr("data-target");
			var $collapse = $(target);
			
			if ($this.hasClass("is-open")) {
				$this.removeClass("is-open");
				$collapse.slideUp(250);
			} else {
				$this.closest('.dyk-nav-list-card').find('.dyk-accordion-toggle').removeClass('is-open');
				$this.closest('.dyk-nav-list-card').find('.dyk-nav-collapse-content').slideUp(250);
				$this.addClass("is-open");
				$collapse.slideDown(250);
			}
		});

		// 3-Column Quick Navigation Trigger Handler
		$(document).on("click", ".dyk-trigger-accordion", function(e) {
			e.preventDefault();
			var target = $(this).attr("data-target");
			if ($(target).length) {
				var $toggle = $('.dyk-accordion-toggle[data-target="' + target + '"]');
				if ($toggle.length) {
					if (!$toggle.hasClass('is-open')) {
						$toggle.trigger('click');
					}
				} else {
					$(target).slideDown(250);
				}
				$('html, body').animate({ scrollTop: $(target).offset().top - 80 }, 300);
			}
		});

		// Kabar Terbaru Read More Toggle (Selengkapnya / Tutup)
		$(document).on("click", ".dyk-kabar-readmore", function(e) {
			e.preventDefault();
			var $this = $(this);
			var $body = $this.prev(".dyk-kabar-body");
			if ($body.hasClass("is-truncated")) {
				$body.removeClass("is-truncated");
				$this.text("Tutup");
			} else {
				$body.addClass("is-truncated");
				$this.text("Selengkapnya");
			}
		});

		// Share button click handler (Sticky and others)
		$(document).on("click", ".dyk-sticky-btn-share, .dyk-open-share, .donation_button_share", function(e) {
			e.preventDefault();
			if (navigator.share) {
				navigator.share({
					title: document.title,
					text: <?php echo json_encode($row->title); ?>,
					url: window.location.href
				}).catch(function(err) {});
			} else {
				if ($('#fixed-share-button').length) {
					$('#fixed-share-button').show();
					$('html, body').animate({ scrollTop: $('#fixed-share-button').offset().top - 100 }, 300);
				} else {
					window.dykCopyDoaLink(window.location.href);
				}
			}
		});



		function set_hide(id){
			$('#'+id+' .plus1').removeClass('show').removeAttr('style');
		}

		// Load Fundariser
		$('.load_fundraiser').bind("click", function(e) {
			var id = $(this).attr('id');
			var campaign_id = $(this).attr('data-id');
			var load_count = $(this).attr('data-count');
			var anonim = $(this).attr('data-anonim');
			var fullanonim = $(this).attr('data-fullanonim');
			$('#'+id).text('Load more...');

			var data_nya = [id, campaign_id, load_count, anonim, fullanonim];
		    var data = {
		        "action": "dykfunction_load_fundraiser",
		        "datanya": data_nya
		    };

		    jQuery.post("<?php echo $home_url; ?>/wp-admin/admin-ajax.php", data, function(response) {
		    	var res_trimmed = (response || '').trim();
		    	if(res_trimmed == ''){
		    		$('#'+id).hide();
					$('#box_btn_'+id+' .loadmore_info').html('No more data').slideDown();
			        setTimeout(function() {
			            $('#box_btn_'+id+' .loadmore_info').hide();
			        }, 5000);
				} else {
			        load_count = parseFloat(load_count)+1;
			        $('#'+id).attr('data-count', load_count).text('Load more');
					$('#box_'+id).append(response);
				}
		    });
		});


		// Load Doa Donatur
		$('.load_doa_donatur').bind("click", function(e) {
			var id = $(this).attr('id');
			var campaign_id = $(this).attr('data-id');
			var load_count = $(this).attr('data-count');
			var anonim = $(this).attr('data-anonim');
			var fullanonim = $(this).attr('data-fullanonim');
			$('#'+id).text('Load more...');

			var data_nya = [id, campaign_id, load_count, anonim, fullanonim];
		    var data = {
		        "action": "dykfunction_load_doa_donatur",
		        "datanya": data_nya
		    };

		    jQuery.post("<?php echo $home_url; ?>/wp-admin/admin-ajax.php", data, function(response) {
		    	var res_trimmed = (response || '').trim();
		    	if(res_trimmed == ''){
		    		$('#'+id).hide();
					$('#box_btn_'+id+' .loadmore_info').html('No more data').slideDown();
			        setTimeout(function() {
			            $('#box_btn_'+id+' .loadmore_info').hide();
			        }, 5000);
				} else {
			        load_count = parseFloat(load_count)+1;
			        $('#'+id).attr('data-count', load_count).text('Load more');
					$('#box_'+id).append(response);
				}
		    });
		});

		// Load Data Donatur
		$('.load_data_donatur').bind("click", function(e) {
			var id = $(this).attr('id');
			var campaign_id = $(this).attr('data-id');
			var load_count = $(this).attr('data-count');
			var anonim = $(this).attr('data-anonim');
			var fullanonim = $(this).attr('data-fullanonim');
			var act = $(this).attr('data-act');
			$('#'+id).text('Load more...');

			var data_nya = [id, campaign_id, load_count, anonim, fullanonim, act];
		    var data = {
		        "action": "dykfunction_load_data_donatur",
		        "datanya": data_nya
		    };

		    jQuery.post("<?php echo $home_url; ?>/wp-admin/admin-ajax.php", data, function(response) {
		    	var res_trimmed = (response || '').trim();
		    	if(res_trimmed == ''){
		    		$('#'+id).hide();
					$('#box_btn_'+id+' .loadmore_info').html('No more data').slideDown();
			        setTimeout(function() {
			            $('#box_btn_'+id+' .loadmore_info').hide();
			        }, 5000);
				} else {
			        load_count = parseFloat(load_count)+1;
			        $('#'+id).attr('data-count', load_count).text('Load more');
					$('#box_'+id).append(response);
				}
		    });
		});

		// DYK Campaign Slider Init
		(function() {
			var slider = document.getElementById('dyk_campaign_slider');
			if (!slider) return;
			var track = slider.querySelector('.dyk-slider-track');
			var slides = slider.querySelectorAll('.dyk-slider-slide');
			var prevBtn = slider.querySelector('.dyk-slider-prev');
			var nextBtn = slider.querySelector('.dyk-slider-next');
			var counter = slider.querySelector('.dyk-current-slide');
			var dots = slider.querySelectorAll('.dyk-slider-dot');
			var currentIndex = 0;
			var total = slides.length;
			var touchStartX = 0;
			var touchEndX = 0;

			function goToSlide(idx) {
				if (idx < 0) idx = total - 1;
				if (idx >= total) idx = 0;
				currentIndex = idx;
				track.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';
				if (counter) counter.textContent = (currentIndex + 1);
				dots.forEach(function(dot, i) {
					dot.classList.toggle('active', i === currentIndex);
				});
			}

			if (prevBtn) {
				prevBtn.addEventListener('click', function(e) {
					e.stopPropagation();
					goToSlide(currentIndex - 1);
				});
			}
			if (nextBtn) {
				nextBtn.addEventListener('click', function(e) {
					e.stopPropagation();
					goToSlide(currentIndex + 1);
				});
			}
			dots.forEach(function(dot) {
				dot.addEventListener('click', function(e) {
					e.stopPropagation();
					var idx = parseInt(this.getAttribute('data-index'), 10);
					goToSlide(idx);
				});
			});

			slider.addEventListener('touchstart', function(e) {
				touchStartX = e.changedTouches[0].screenX;
			}, { passive: true });

			slider.addEventListener('touchend', function(e) {
				touchEndX = e.changedTouches[0].screenX;
				var diff = touchStartX - touchEndX;
				if (Math.abs(diff) > 40) {
					if (diff > 0) goToSlide(currentIndex + 1);
					else goToSlide(currentIndex - 1);
				}
			}, { passive: true });
		})();

		// DYK Video Parallax Scroll Effect
		(function() {
			var videoCover = document.getElementById('dyk_video_cover');
			var videoParallax = document.getElementById('dyk_video_parallax');
			if (!videoCover || !videoParallax) return;

			var ticking = false;
			function updateParallax() {
				var rect = videoCover.getBoundingClientRect();
				var winH = window.innerHeight || document.documentElement.clientHeight;
				if (rect.bottom > 0 && rect.top < winH) {
					var scrolled = window.pageYOffset || document.documentElement.scrollTop;
					var speed = 0.25;
					var yOffset = scrolled * speed;
					videoParallax.style.transform = 'translate3d(0, ' + yOffset + 'px, 0)';
				}
				ticking = false;
			}

			window.addEventListener('scroll', function() {
				if (!ticking) {
					window.requestAnimationFrame(updateParallax);
					ticking = true;
				}
			}, { passive: true });
		})();

	</script>

	<?php if($gtm_id!=''){ ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $gtm_id;?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    
    <?php } ?>
</body>
</html>
