<?php

	global $wpdb;
	global $wp;
    $table_name = $wpdb->prefix . "dyk_campaign";
    $table_name2 = $wpdb->prefix . "dyk_donate";
    $table_name3 = $wpdb->prefix . "dyk_users";
    $table_name4 = $wpdb->prefix . "dyk_love";
    $table_name5 = $wpdb->prefix . "dyk_settings";
    $table_name6 = $wpdb->prefix . "dyk_campaign_update";

    // Check IP User
    check_blocked_ip();

    // Settings
    $query_settings = $wpdb->get_results('SELECT data from '.$table_name5.' where type="app_name" or type="theme_color" or type="powered_by_setting" ORDER BY id ASC');
    $app_name	 = $query_settings[0]->data;
    $general_theme_color = json_decode($query_settings[1]->data, true);
    $powered_by_setting = $query_settings[2]->data;

    $theme_color 			= !empty($general_theme_color['color'][0]) ? $general_theme_color['color'][0] : '#10a8e5';
	$progressbar_color  	= !empty($general_theme_color['color'][1]) ? $general_theme_color['color'][1] : '#10a8e5';
	$button_color 			= !empty($general_theme_color['color'][2]) ? $general_theme_color['color'][2] : '#10a8e5';
	$button_color2 			= !empty($general_theme_color['color'][3]) ? $general_theme_color['color'][3] : '#10a8e5';

	if(empty($theme_color) || $theme_color=='#009F61'){
    	$theme_color = '#10a8e5';
    }
    if(empty($progressbar_color) || $progressbar_color=='#009F61' || $progressbar_color=='#24CC63'){
    	$progressbar_color = '#10a8e5';
    }
    if(empty($button_color) || $button_color=='#7680FF' || $button_color=='#7680ff'){
    	$button_color = '#10a8e5';
    }
    if(empty($button_color2) || $button_color2=='#7680FF' || $button_color2=='#7680ff'){
    	$button_color2 = '#10a8e5';
    }

	$s = '';
    if (isset($_GET['s'])) {
		$s = $_GET['s'];
	}

	$t = '';
    if (isset($_GET['t'])) {
		$t = $_GET['t'];
	}

	// echo $s.'_oke';

	$home_url = get_site_url();

?>
<!-- Powered by DonasiYuk.id -->
<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=0">
	<meta name="application-name" content="<?php echo $home_url; ?>"/>
	<meta property="og:url" content="<?php echo $home_url; ?>" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="Search Campaign" />
	<meta property="og:description" content="Search Campaign" />
	<meta property="og:image" content="<?php echo plugin_dir_url( __FILE__ ).'admin/images/cover_donasiyuk.jpg'; ?>" />
	<title>Search Campaign</title>
	<?php dyk_set_favicon(); ?>
	<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ) . 'assets/css/donasiyuk.css';?>">
	<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ) . 'assets/css/donasiyuk-style.css';?>">
	<link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ) . 'assets/css/lucide-fix.css';?>">
	<!-- Google Material Icons font removed: icons migrated to Lucide -->

	<style type="text/css">
		.card__content.content_2 {
			height: auto;
		}
		a:active, a:focus, a:visited {
	      box-shadow: none !important;
	      outline: none;
	      box-shadow: 0 4px 15px 0 rgba(0,0,0,.1);
	    }
	    .cards__campaign.cards__list .cards__item .card__content.content_2 {
	    	padding-top: 0;
	    }
	    .cards__campaign.cards__list .cards__item {
	    	box-shadow: none;
	    	margin:0;
	    	margin-top:20px;
	    }
	    .cards__campaign.cards__list .cards__item a {
	    	text-decoration: none;
	    }
	    
	    #section-campaign {
	    	padding-bottom: 50px;
	    }
	    .campaign-header-title2 {
	    	color: transparent;
	    }
	    .campaign-header-title2.show-title {
	    	color: #343434;
	    }
	    .section-image {
	    	/*margin-top: 51px;*/
	    }
	    @media only screen and (max-width:480px) {
		    #section-campaign {
		    	padding: 0 !important;
		    	padding-bottom: 20px !important;
		    }
		    .section-title {
		    	padding:20px;
		    	padding-top: 20px;
		    }
		    .cards__campaign {
		    	/*margin-top: -40px;*/
		    }
		    .cards__campaign.cards__list .cards__item {
		    	padding-left: 20px;
		    	margin-top: 0;
		    }
		    .cards__campaign.cards__list .cards__item .card__content.content_2 {
		    	height: 95px;
		    }
		}

		#section-campaign .cards__campaign.cards__list .cards__item {
			padding:0;
			border: none;
		}
		.cards__campaign.cards__list .cards__item .card__image {
			margin:0;
		}
		.cards__campaign.cards__list .cards__item .card__image {
		    width: 27%;
		}
		.cards__campaign.cards__list .cards__item .card__content.content_1 {
		    margin-left: 28%;
		}
		.card__content.content_1 {
		    padding-top: 0px;
		}
		#header-title {
			background: #fff;
			height: 40px;
			background: #2c8275;
			border-radius: 4px;
			
		}
		#header-title.flying-header {
			/*background: #18a870;*/
		}
		#search-box, #search-box2 {
			width: 75%;
			margin-left: 12%;
			height: 35px;
			padding-left: 20px;
			padding-right: 20px;
			font-size: 14px;
			border: none;
			border-radius: 4px;
			background: <?php if($theme_color!=''){echo $theme_color;}else{echo'#18a870';}?>;
			color: #fff;
		}
		.btn_loadmore_list button, .btn_loadmore_grid button {
		  background: #fff;
		  color: <?php echo $button_color?>;
		  border: 1px solid <?php echo $button_color?>;
		}

		#section-campaign{ 
			margin-top: 90px;
			border-radius: 4px;
		}
		h3.section-title {
			margin-bottom: 5px;
		}

		.card__title {
			display: inline;
			line-height: 0px !important;
		}
		.cards__campaign.cards__list .cards__item:hover {
			-webkit-box-shadow: none;
		    box-shadow: none;
		    transition: none;
		}
		.cards__campaign.cards__list .cards__item:hover .card__image img {
			-webkit-box-shadow: 0 8px 16px rgba(0,0,0,0.2);
	    	box-shadow: 0 8px 16px rgba(0,0,0,0.2);
	    	transition: border .2s linear, transform .2s linear, background-color .2s linear, box-shadow .2s linear, opacity .2s linear, -webkit-transform .2s linear, -webkit-box-shadow .2s linear;

		}
		.cards__campaign.cards__list .cards__item .card__content.content_1 {
			padding-top: 5px;
		}

		/* search */
		body.search-active {
		  overflow: hidden;
		}
		body.search-active .search-input {
		  opacity: 1;
		  transform: none;
		  pointer-events: all;
		}
		body.search-active .icon-close {
		  opacity: 1;
		  transform: rotate(-90deg);
		}
		body.search-active .control {
		  cursor: default;
		}
		body.search-active .control .btn-material {
		  transform: scale(80);
		}
		body.search-active .control .icon-material-search {
		  opacity: 0;
		}

		/* Close Icon */
		.icon-close {
		  position: fixed;
		  top: 30px;
		  right: 30px;
		  color: #FFF;
		  cursor: pointer;
		  font-size: 70px;
		  opacity: 0;
		  transition: all 0.3s ease-in-out;
		}
		.icon-close:hover {
		  transform: rotate(0);
		}

		/* Search Input */
		.search-input {
		  height: 80px;
		  position: absolute;
		  top: 50%;
		  left: 50px;
		  margin-top: -40px;
		  pointer-events: none;
		  opacity: 0;
		  transform: translate(40px, 0);
		  transition: all 0.3s ease-in-out;
		}
		.search-input input {
		  color: #fff;
		  font-size: 54px;
		  border: 0;
		  background: transparent;
		  -webkit-appearance: none;
		  box-sizing: border-box;
		  outline: 0;
		  font-weight: 200;
		}
		.search-input ::-webkit-input-placeholder {
		  color: #EEE;
		}
		.search-input :-moz-placeholder {
		  color: #EEE;
		  opacity: 1;
		}
		.search-input ::-moz-placeholder {
		  color: #EEE;
		  opacity: 1;
		}
		.search-input :-ms-input-placeholder {
		  color: #EEE;
		}

		/* Container */
		.container {
		  padding-right: 50px;
		  padding-left: 50px;
		  position: relative;
		}
		.container.container-dark {
		  background: #22313F;
		  color: #FFF;
		}

		/* Control btn */
		.control {
		  cursor: pointer;
		}
		.control .btn-material {
		  position: absolute;
		  width: 60px;
		  height: 60px;
		  right: 0px;
		  top: -60px;
		  border-radius: 100%;
		  box-sizing: border-box;
		  background: #18a870;
		  outline: 0;
		  transform-origin: 50%;
		  transition: all 0.7s cubic-bezier(0.4, 0, 0.2, 1);
		}
		.control .btn-material:hover {
		  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
		}
		.control .icon-material-search {
		  color: #FFF;
		  position: absolute;
		  top: -10px;
		  right: 78px;
		  transition: opacity 0.3s ease-in-out;
		}

		.icon-close.material-icons {
			z-index: 1;
		}
		input::placeholder {
  			color: #ffffff;
  		}

  		.powered-donasiyuk-img {
  			position: inherit !important;
	    }
	    #header-title.flying-header span img {
		  width: 18px;
		  filter: brightness(0) invert(0.4);
		}

		@media only screen and (max-width:480px) {
			#section-campaign{
				padding-left: 10px;
			}
			#section-campaign .cards__campaign.cards__list .cards__item {
				padding-left: 15px;
			}
			#search-box {
				width: 68%;
				margin-left: 16%;
			}
			.search-input {
				left:0;
			}
			.search-input input {
				width: 320px;
				padding-left: 20px;
				padding-right: 20px;
				font-size: 48px;
			  }
			.icon-close { 
				position: absolute;
			}
		}


		
	</style>


</head>
<body>
	<?php
	

	?>
	

	<div id="header-title" class="section-box flying-header">
		<span class="nav-icon"><a href="<?php echo get_site_url();?>"><img alt="Image" src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/home.png'; ?>" style="padding-top: 9px;"></a></span>
		<i class="icon-close material-icons"></i>
		<?php if($t=='default'){ ?> 
			<span class="donasiyuk_search2" style="margin-left: 0;">
			<input type="text" placeholder="Search Campaign" id="search-box2" value="<?php echo $s; ?>">
			</span>
		<?php }else{ ?>
			<input type="text" placeholder="Search Campaign" id="search-box" value="<?php echo $s; ?>">
		<?php } ?>
	</div>

  	<div class='control'>
    	<div class='btn-material'></div>
  	</div>

	<!-- full screen form controls -->
	<i class='icon-close material-icons'>close</i>
	<div class='search-input'>
	  	<input class='input-search' placeholder='Start Typing' type='text' value="<?php echo $s; ?>">
	</div>

	<?php


	// if($row->publish_status==0){
    //     $pub_status = 'Draft';
    // }elseif($row->publish_status==1){
    //     $pub_status = 'Published';
    // }elseif($row->publish_status==2){
    //     $pub_status = 'Pending Review';
    // }elseif($row->publish_status==3){
    //     $pub_status = 'Archived';
    // }elseif($row->publish_status==4){
    //     $pub_status = 'Unlisted';
    // }else{
    //     $pub_status = '';
    // }


	// $user_id = $profile[0]->user_id;
	$campaign = $wpdb->get_results("SELECT * FROM $table_name where (title like '%$s%' or information like '%$s%') and publish_status='1' ORDER BY id ASC LIMIT 0,5 ");

	$hasil_list = '';

	$count_campaign = $wpdb->get_results("SELECT id FROM $table_name where (title like '%$s%' or information like '%$s%') and publish_status='1' ORDER BY id");
	$jumlah_campaign = count($count_campaign);

	foreach ($campaign as $value) {

            if($value->publish_status=='1'){
                $campaign_url = get_site_url().'/campaign/'.$value->slug;
            }else{
                $campaign_url = get_site_url().'/preview/'.$value->slug;
            }

            if($value->image_url==null){
                $image_url = plugin_dir_url( __FILE__ ).'assets/images/cover_donasiyuk.jpg';
            }else{
                $image_url = $value->image_url;
            }

            // GET PROFILE PICTURE CAMPAIGNER
			$profile = $wpdb->get_results('SELECT user_pp_img as photo, user_verification from '.$table_name3.' where user_id="'.$value->user_id.'"');
			$verified_user = '';
			if(!empty($profile[0]->user_verification)){
				if($profile[0]->user_verification=='1'){
	            	$verified_user = '<span class="verified_checklist" title="Verified" style="display:inline-flex;align-items:center;vertical-align:middle;margin-left:4px;"><svg width="15" height="15" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.407 1.689c-.293-1.585-2.471-1.585-2.764 0-.207 1.124-1.517 1.577-2.328.804l-.126-.12c-1.121-1.068-2.83.273-2.157 1.693.485 1.021-.316 2.195-1.402 2.052l-.103-.014C.031 5.906-.607 8.022.73 8.751c.967.528.967 1.97 0 2.498-1.336.729-.698 2.845.798 2.647l.103-.014c1.086-.143 1.887 1.03 1.402 2.052-.674 1.42 1.036 2.761 2.157 1.693l.126-.12c.81-.773 2.12-.32 2.328.804.293 1.585 2.471 1.585 2.764 0 .207-1.124 1.517-1.577 2.328-.804l.126.12c1.121 1.068 2.83-.273 2.157-1.693-.485-1.021.316-2.195 1.402-2.052l.103.014c1.496.198 2.134-1.918.798-2.647-.968-.528-.968-1.97 0-2.498 1.336-.729.698-2.845-.798-2.647l-.103.014c-1.086.143-1.887-1.03-1.402-2.052.673-1.42-1.036-2.761-2.157-1.692l-.126.12c-.81.772-2.12.32-2.328-.805Zm1.534 5.665c.452.337.544.975.204 1.424L9.21 12.443a1.026 1.026 0 0 1-1.543.109L6 10.814c-.4-.397-.4-1.042 0-1.44a1.029 1.029 0 0 1 1.449 0l.83.909 2.228-2.726c.339-.45.98-.54 1.434-.203Z" fill="#10A8E5"></path></svg></span>';
	            }
			}
            

            // GET USER NAME
			$args2 = array( 'blog_id' => 0, 'search' => $value->user_id, 'search_columns' => array( 'ID' ) );
            $get_name = get_users( $args2 );

            if($get_name==null){
                $campaigner_name = '-';
            }else{
                $nama_user = str_replace("'", "", $get_name[0]->user_firstname.' '.$get_name[0]->user_lastname);
                $campaigner_name = $nama_user; // nama asli
            }

            $hasil_list .= '
            <li class="cards__item">
			  <a href="'.$campaign_url.'">
		      <div class="card__">
		        <div class="card__image"><img src="'.$image_url.'"></div>
		        <div class="card__content content_1">
		          <h2 class="card__title"><span style="font-size:13px;">'.$value->title.'<br><span style="font-size: 12px;font-weight: normal;">'.$campaigner_name.$verified_user.'</span></span></h2>
		          <br>
		        </div>
		      </div>
		      </a>
		    </li>
		    ';
			
		}

		?>
	


	<div class="section-box" id="section-campaign">
		<h3 class="section-title">Campaign (<?php echo $jumlah_campaign; ?>)</h3>
		<div style="font-size: 15px;line-height: 1.6;">
		<?php
			// $id = $user_randid;
			$id = 0;
			echo '<ul id="section_'.$id.'" class="cards__campaign cards__list">'.$hasil_list.'</ul>';
			if($jumlah_campaign>5){
				echo '<div id="box_button_'.$id.'" class="btn_loadmore_list" style="margin-top:40px;"><div class="donasiyuk_loadmore_info"></div><button class="load_campaign" data-id="'.$id.'" data-count="2">Loadmore</button></div>';
			}

		?>
		</div>
	</div>

	<?php if($powered_by_setting=='1'){ ?>
	<div class="section-box box-powered">
		<div class="powered-donasiyuk-box"><a href="https://donasiyuk.id" target="_blank">Powered by DonasiYuk</a></div>
	</div>
	<?php } ?>

	<?php
	if($theme_color!=''){
    	echo '<style>.control .btn-material{background: '.$theme_color.';}</style>';
    }

    ?>
	<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> -->
	<script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/jquery.min.js';?>"></script>
	<!-- Lucide icons (replaces FontAwesome/MDI/Dripicons) -->
	<script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/lucide.min.js';?>"></script>
	<script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/lucide-init.js';?>"></script>
	<!-- <script src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/js/donasiyuk.min.js';?>"></script> -->
	<script>


	(function($) {
	    $.fn.onEnter = function(func) {
	        this.bind('keypress', function(e) {
	            if (e.keyCode == 13) func.apply(this, [e]);    
	        });               
	        return this; 
	     };
	})(jQuery);
		        

	jQuery(document).ready(function($) {

		$('#search-box').click( function(){
		  $(this).hide();
		  $('#header-title').attr('style', 'display: none !important;');
		  $('body').addClass('search-active');
		  $('.input-search').focus();
		});

		$('.icon-close').click( function(){
		  $('body').removeClass('search-active');
		  $('#header-title').show();
		  $('#search-box').slideDown('slow');
		});

		$(".input-search").onEnter( function() {
		    var s = $(this).val();
		    var siteURl = "<?php echo get_site_url(); ?>";
		    var linkRedirect = siteURl+'/search_campaign/s?s='+s;
		    var redirectWindow = window.open(linkRedirect, "_self");
	        redirectWindow.location;     
		});

		$('.input-search').keyup(function() {
            var s = $(this).val();
            $('#search-box').attr('value', s).val(s);
        });

        $(document).on("keyup",".donasiyuk_search2 input",function(e){
			if (e.which == 13) {
				var s = $(this).val();
				var siteURl = "<?php echo get_site_url(); ?>";
			    var linkRedirect = siteURl+'/search_campaign/s?s='+s+'&t=default';
			    var redirectWindow = window.open(linkRedirect, "_self");
		        redirectWindow.location;
			}    
		});

		$(".load_campaign").bind("click", function(){
			var id = $(this).attr('data-id');
			var s = $('.input-search').val();
			var load_count = $(this).attr('data-count');
			$(this).text('Load more...');

			var data_nya = [s, load_count];
		    var data = {
		        "action": "donasiyuk_load_campaign_search",
		        "datanya": data_nya
		    };

		    jQuery.post("<?php echo $home_url; ?>/wp-admin/admin-ajax.php", data, function(response) {

		    	if(response==''){
					$('#box_button_'+id+' .donasiyuk_loadmore_info').text('No more data').slideDown();
			        setTimeout(function() {
			            $('#box_button_'+id+' .donasiyuk_loadmore_info').hide()
			        }, 5000);
			        $('#box_button_'+id+' button').text('Loadmore');
				}else{
					load_count = parseFloat(load_count)+1;
			        $('#box_button_'+id+' button').attr('data-count', load_count);
			        $('#box_button_'+id+' button').text('Loadmore');

					$('#section_'+id).append(response).slideDown();
				}

		    });
		});

	});

	</script>
</body>
</html>

