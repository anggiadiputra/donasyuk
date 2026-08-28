<?php

function donasiyuk_mydonate() {
    ?>
    <?php 
        global $wpdb;
        $table_name = $wpdb->prefix . "dyk_campaign";
        $table_name2 = $wpdb->prefix . "dyk_category";
        $table_name3 = $wpdb->prefix . "dyk_campaign_update";
        $table_name4 = $wpdb->prefix . "dyk_donate";
        $table_name5 = $wpdb->prefix . "dyk_settings";
        $table_name6 = $wpdb->prefix . "dyk_users";


        // ROLE
        $cap = get_user_meta( wp_get_current_user()->ID, $wpdb->get_blog_prefix() . 'capabilities', true );
        $roles = array_keys((array)$cap);
        $role = $roles[0];

        $id_login = wp_get_current_user()->ID;

        if(isset($_GET['action'])){
            if($_GET['action']=="settings"){
                $settings = true;
            }else{
                $settings = false;
            }
        }else{
            $settings = false;
        }

        $cap = get_user_meta( wp_get_current_user()->ID, $wpdb->get_blog_prefix() . 'capabilities', true );
        $roles = array_keys((array)$cap);
        $role = $roles[0];

        // category
        $row2 = $wpdb->get_results('SELECT * from '.$table_name2.' ');     

        // Settings
        $query_settings = $wpdb->get_results('SELECT data from '.$table_name5.' where type="form_setting" or type="btn_followup" or type="text_f1" or type="text_f2" or type="text_f3" or type="text_f4" or type="text_f5"  ORDER BY id ASC');
        $form_setting = $query_settings[0]->data;
        $btn_followup = $query_settings[1]->data;
        $text_f1 = $query_settings[2]->data;
        $text_f2 = $query_settings[3]->data;
        $text_f3 = $query_settings[4]->data;
        $text_f4 = $query_settings[5]->data;
        $text_f5 = $query_settings[6]->data;

        // Currency
        $query_currency = $wpdb->get_results('SELECT data from '.$table_name5.' where type="currency"  ORDER BY id ASC');
        $currency = $query_currency[0]->data;
        $lang = get_data_lang($currency);
        $langArray = require_once(ROOTDIR_DYK . 'library/locale/'.$lang.'.php');
        
        $show_currency = donasiyuk_currency($currency);
        $show_currency2 = donasiyuk_currency2($currency);
        
    ?>


    <!-- DataTables -->
    <link href="<?php echo plugin_dir_url( __FILE__ ); ?>plugins/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo plugin_dir_url( __FILE__ ); ?>plugins/datatables/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <!-- Responsive datatable examples -->
    <link href="<?php echo plugin_dir_url( __FILE__ ); ?>plugins/datatables/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" /> 
    <!-- App css -->
    <link href="<?php echo plugin_dir_url( __FILE__ ); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo plugin_dir_url( __FILE__ ); ?>assets/css/jquery-ui.min.css" rel="stylesheet">
    <!-- icons.min.css removed: icons migrated to Lucide (lucide.min.js loaded below) -->
    <link href="<?php echo plugin_dir_url( __FILE__ ); ?>assets/css/app.min.css" rel="stylesheet" type="text/css" />

    <!-- jQuery -->
    <script src="<?php echo plugin_dir_url( __FILE__ ); ?>assets/js/jquery.min.js"></script>
    <script src="<?php echo plugin_dir_url( __FILE__ ); ?>assets/js/jquery-ui.min.js"></script>

    <?php 

    // wp_enqueue_script('jquery');
    // This will enqueue the Media Uploader script
    wp_enqueue_media();
        
    ?>

    <style>
    /* =========================================================================
       CLASSIC & COMPACT MYDONATE STYLES
       ========================================================================= */
    body {
        background-color: #f8fafc !important;
        color: #1e293b !important;
    }
    .dyk-compact-stat-card {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px 0 rgba(0, 0, 0, 0.02) !important;
        margin-bottom: 16px !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
        border-bottom: none !important;
    }
    .dyk-compact-stat-card:hover {
        box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.06), 0 2px 4px 0 rgba(0, 0, 0, 0.03) !important;
        transform: translateY(-1px) !important;
    }
    .dyk-compact-stat-card .card-body {
        padding: 16px 18px !important;
    }
    .dyk-stat-label {
        font-size: 12px !important;
        font-weight: 600 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        letter-spacing: 0.6px !important;
        margin: 0 !important;
    }
    .dyk-stat-badge {
        width: 34px !important;
        height: 34px !important;
        border-radius: 8px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
    }
    .dyk-stat-badge svg {
        width: 17px !important;
        height: 17px !important;
    }
    .badge-emerald {
        background: #ecfdf5 !important;
        color: #059669 !important;
    }
    .badge-blue {
        background: #eff6ff !important;
        color: #2563eb !important;
    }
    .badge-purple {
        background: #faf5ff !important;
        color: #7c3aed !important;
    }
    .badge-rose {
        background: #fff1f2 !important;
        color: #e11d48 !important;
    }
    .dyk-stat-value {
        font-size: 22px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin: 6px 0 4px 0 !important;
        letter-spacing: -0.5px !important;
        line-height: 1.25 !important;
    }
    .dyk-stat-subtext {
        font-size: 12px !important;
        color: #64748b !important;
        line-height: 1.4 !important;
    }
    .card {
        border: 1px solid #e2e8f0 !important;
        border-radius: 10px !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04) !important;
        background: #ffffff !important;
    }
    .header-title {
        font-size: 15px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        margin-bottom: 12px !important;
        letter-spacing: -0.2px !important;
    }
    .row [class^="col"] {
        margin:0;
    }
    .notice, #message, #dolly, #wpbody-content .promotion {
        display:none;
    }
    .set_payment.received span {
        color:#36BD47;
    }
    .set_payment.waiting span {
        color:#E1345E;
    }
    #box-section {
        margin: 0 auto;
        margin-top: 20px;
        max-width: 540px;
    }
    a.detail_donasi i {
        color: #91a2b0 !important;
        margin-right: 3px;
    }
    a.detail_donasi span {
        font-size:10px;
        color:#91a2b0;
    }

    a.detail_donasi:hover span, a.detail_donasi:hover i {
        color: #36BD47 !important;
    }
    .custom-control-input:checked ~ .custom-control-label::before {
        color: #fff;
        border-color: #36bd47;
        background-color: #36bd47;
    }
    .custom-control-label::before {
        border: #d8204c solid 1px;
    }
    .custom-switch .custom-control-label::after {
        background-color: #d8204c;
    }
    .btn-followup {
        background:#D8204C;
        border-color: #D8204C;
        padding: 2px 8px;
    }
    .btn-followup:hover {
        background:#FD003C;
        border-color: #FD003C;
    }
    .btn-followup.sent {
        background:#36BD47;
        border-color: #36BD47;
    }
    .btn-followup.sent:hover {
        background:#1ACE31;
        border-color: #1ACE31;
    }
    .btn-followup .spinner-border {
        width: 11px;
        height: 11px;
    }
    .target_tak_hingga {
        font-size: 16px;position: absolute;margin-top: -2px;margin-left: 3px;
    }
    .field_required {
        color: #ff3b3b;
    }
    .media:hover {
        background: #f6faff;
    }
    .f_edit, .f_delete {
        cursor: pointer;
        float: right;
    }
    .f_delete {
        margin-left: 5px;
    }
    .campaign-title {
        font-size: 14px;
        font-weight: bold;
        color: #384d64;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .campaign-title a:hover {
        /*color: #7680FF;*/
        /*color: #2196f3;*/
        color: #52649b;
    }    
    input.set_red, input.form-control.set_red, img.set_red, .mce-edit-area.set_red {
        border: 2px solid #ED8181 !important;
    }
    .wp-core-ui select, div.dataTables_wrapper div.dataTables_filter input {
        border-color: #e5eaf0;
    }
    div.dataTables_wrapper div.dataTables_filter input:visited, div.dataTables_wrapper div.dataTables_filter input:active, div.dataTables_wrapper div.dataTables_filter input:focus {
        border-color: #e5eaf0;
    }
    .error.landingpress-message{
        display: none;
    }
    .page-content-tab {
        margin: 0 !important;
        width: auto;
    }
    img.thumb-cover {
        height: 60px;
        border-radius: 4px;
    }
    .active-status {
        /*background: #1CB65D;*/
        background: #36BD47;
        color: #fff;
        border-radius: 4px;
        padding: 2px 8px;
        font-size: 9px;
    }
    table.dataTable td {
        font-size: 12px;
        vertical-align: top;
        padding-top: 15px;
        color: #384d64;
    }
    table.dataTable td img {
        margin-top: 3px;
    }
    button.no-border {
        border: 0;
        background: #f6f9ff;
    }
    button.no-border:hover {
        background: #9eb5ca;
        color: #ffffff;
    }
    button.no-border.delete_campaign:hover {
        background: #F05860;
        color: #ffffff;
    }
    .btn-group button.btn {
        padding: .175rem .75rem;
    }
    
    a:active, a:focus, a:visited {
      box-shadow: none !important;
      outline: none;
      box-shadow: 0 4px 15px 0 rgba(0,0,0,.1);
    }
    input.form-control {
        border: 1px solid #e8ebf3 !important;
        font-size: 14px;
    }
    input.form-control:active, input.form-control:visited {
      border: 1px solid #7680FF !important;
      box-shadow: none !important;
      outline: none;
    }
    .mce-menubar, .mce-branding {
        display: none;
    }
    #cover_image img {
        border-radius: 4px;
    }
    .fro-profile_main-pic-change {
        cursor: pointer;
        background-color: #7680ff;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-align: center;
        -ms-flex-align: center;
        align-items: center;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        justify-content: center;
        -webkit-box-flex: 1;
        -ms-flex: 1;
        flex: 1;
        -webkit-box-shadow: 0px 0px 20px 0px rgba(252, 252, 253, 0.05);
        box-shadow: 0px 0px 20px 0px rgba(252, 252, 253, 0.05);
        position: absolute;
        right: 44%;
        top: 82%;
        transition: all .35s ease;
    }

    .fro-profile_main-pic-change:hover {
        background-color: #505DFF;
    }
    .fro-profile_main-pic-change i {
        color: #fff;
    }

    .form-group input {
        height: 45px;
    }

    .target .currency {
        position: absolute;
        margin-top: -37px;
        margin-left: 15px;
        font-weight: bold;
        font-size: 18px;
        color: #719eca;
    }
    #packaged .currency {
        position: absolute;
        margin-top: -27px;
        margin-left: 10px;
        font-weight: bold;
        font-size: 14px;
        color: #719eca;
    }
    #packaged input {
        text-align: right;
    }
    .opt_packaged {
        display: none;
    }
    .opt_packaged.show {
        display: inline;
    }
    .target input {
        text-align: right;
        font-size: 24px;
        font-weight: bold;
        color: #23374d;
    }
    .box-slugnya {
        background: #e3eaf2;
        padding: 1px 4px;
        border-radius: 2px;
    }
    .box-slugnya[contenteditable="true"] {
        border: 1px solid #7680ff;
        background: #fff;
        padding: 1px 6px;
    }
    .copylink {
        font-size: 16px;
        margin-right: 5px;
        padding-top: 3px;
        cursor: pointer;
    }
    .copylink:hover {
        color:#505DFF;
    }
    .edit-slug, .edit-status, .edit-visibility {
        font-size: 16px;
        margin-left: 5px;
        padding-top: 3px;
        cursor: pointer;
    }
    .edit-slug:hover, .edit-status:hover, .edit-visibility:hover  {
        color:#505DFF !important;
    }
    #publish_status {
        display: none;
        margin-bottom: 5px;
    }

    #publish-section select {
        height: 30px !important;font-size: 13px;margin-top: 5px;
    }
    .page-title-box {
        padding-bottom: 0;
    }

    .button-hide {
        visibility: hidden;
    }


    /*==================================
        Alert container
    ====================================*/
    #lala-alert-container {
        position: fixed;
        height: auto;
        max-width: 350px;
        width: 100%;
        top: 18px;
        right: 5px;
        z-index: 9999;
    }

    #lala-alert-wrapper {
        height: auto;
        padding: 15px;
    }

    /*==================================
        Alerts
    ====================================*/

    .lala-alert {
        position: relative;
        padding: 25px 30px 20px;
        font-size: 15px;
        margin-top: 15px;
        opacity: 1;
        line-height: 1.4;
        border-radius: 3px;
        border: 1px solid transparent;
        cursor: default;
        transition: all 0.5s ease-in-out;   /* Edit for fadeout time */
        -webkit-user-select: none;  /* Chrome all / Safari all */
        -moz-user-select: none;     /* Firefox all */
        -ms-user-select: none;      /* IE 10+ */
        user-select: none;          /* Likely future */
    }

    .lala-alert span {
        opacity: 0.7;
        transition: all 0.25s ease-in-out;   /* Edit for fadeout time */
    }

    .lala-alert span:hover {
        opacity: 1.0;
    }

    .alert-success {
        color: #ffffff;
        background-color: #37c1aa;
    }

    .alert-success > span {
        color: #0b6f5e;
    }

    .alert-info {
        color: #ffffff;
        background-color: #3473c1;
    }

    .alert-info > span {
        color: #1e4567;
    }

    .alert-warning {
        color: #6b7117;
        background-color: #ffee9e;
    }

    .alert-warning > span {
        color: #8a6d3b;
    }

    .alert-danger {
        color: #ffffff;
        background-color: #d64f62;
    }

    .alert-danger > span {
        color: #6f1414;
    }

    .close-alert-x {
        position: absolute;
        float: right;
        top: 10px;
        right: 10px;
        cursor: pointer;
        outline: none;
    }

    .fade-out {
        opacity: 0;
    }

    /*==================================
        Alert Animation
    ====================================*/
    .animation-target {
      animation: animation 1500ms linear both;
    }

    /* Generated with Bounce.js. Edit at http://goo.gl/BKCT19 */

    @keyframes animation {
      0% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 250, 0, 0, 1); }
      3.14% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 160.737, 0, 0, 1); }
      4.3% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 132.565, 0, 0, 1); }
      6.27% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 91.357, 0, 0, 1); }
      8.61% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 51.939, 0, 0, 1); }
      9.41% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 40.599, 0, 0, 1); }
      12.48% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 6.498, 0, 0, 1); }
      12.91% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 2.807, 0, 0, 1); }
      16.22% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, -17.027, 0, 0, 1); }
      17.22% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, -20.404, 0, 0, 1); }
      19.95% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, -24.473, 0, 0, 1); }
      23.69% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, -21.178, 0, 0, 1); }
      27.36% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, -13.259, 0, 0, 1); }
      28.33% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, -11.027, 0, 0, 1); }
      34.77% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0.142, 0, 0, 1); }
      39.44% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 2.725, 0, 0, 1); }
      42.18% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 2.675, 0, 0, 1); }
      56.99% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, -0.202, 0, 0, 1); }
      61.66% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, -0.223, 0, 0, 1); }
      66.67% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, -0.104, 0, 0, 1); }
      83.98% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0.01, 0, 0, 1); }
      100% { transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1); }
    }


    .breadcrumb-item.active {
        color: #a1b3ca;
    }

    @media only screen and (max-width:767px) {
        .page-title-box .breadcrumb {
            display: inline-flex !important;
            width: 100% !important;
        }
    }


    @media only screen and (max-width:480px) {
        .dyk_label {
            width: auto;
        }
        .page-content-tab, .container-fluid {
            padding: 0;
        }
        .container-fluid .col-lg-4 {
            padding-right: 0;
        }
        .row .col-12 {
            padding-right: 0;
        }
        #update_text_followup {
            width: 100%;
        }
        .col-total-donasi .card, .col-jumlah-donasi .card {
            padding-bottom: 0px;
            margin-bottom: 5px;
        }
    }

    .swal2-popup.swal2-modal{
        border-radius:12px;
        padding: 40px 40px 50px 40px;
        background: url('<?php echo plugin_dir_url( __FILE__ ).'../assets/images/bg4.png'; ?>') no-repeat, #fff;
    }
    button.swal2-close {
        color:#fff;
    }

    
    </style>

    <?php check_license(); ?>

        <?php 

            $id_login = wp_get_current_user()->ID;

            $rows = $wpdb->get_results("SELECT * from $table_name where user_id=$id_login ORDER BY id DESC");

            $total_donasi = $wpdb->get_results("SELECT SUM(nominal) as total, COUNT(id) as jumlah FROM $table_name4 where user_id='$id_login' and status='1' ")[0];
            $nominal_donasi_terkumpul = $total_donasi->total;
            $jumlah_donasi_terkumpul = $total_donasi->jumlah;

            $total_donasi_semua = $wpdb->get_results("SELECT COUNT(id) as jumlah FROM $table_name4 where user_id='$id_login' ")[0];
            $jumlah_donasi_semua = $total_donasi_semua->jumlah;

            if($jumlah_donasi_semua>=1){
                $konversi = $jumlah_donasi_terkumpul/$jumlah_donasi_semua;
            }else{
                $konversi = 0;
            }

            $konversi = $konversi*100;
            $konversi = number_format_currency_percentage($konversi).' %';

            // GET CAMPAIGN ACTIVE
            $active_campaign = 0;
            foreach ($rows as $row) {
                                                
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

                if($hasil->invert==true && $row->publish_status==1){
                    
                }else{
                    if($row->publish_status==1){
                        $active_campaign++;
                    }
                }
            }

            // GET DATA DONASI
            $data_donasi = $wpdb->get_results("SELECT * FROM $table_name4 where user_id='$id_login' ORDER BY id DESC");

            // GET DATA CAMPAIGN
            $data_donasi2 = $wpdb->get_results("SELECT campaign_id FROM $table_name4 where user_id='$id_login' GROUP BY campaign_id ");
            

        ?>

        <div class="body-nya" style="margin-top:20px;margin-right:20px;">

            <!-- Page Content-->
            <div class="page-content-tab">

                <div class="container-fluid">
                    <!-- end page title end breadcrumb -->
                    <?php 
                    $c_license = c_expired_license();
                    echo $c_license;
                    ?>
                    <div class="row"> 
                        <div class="col-lg-4 col-total-donasi">
                            <div class="card dyk-compact-stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="dyk-stat-label"><?php echo get_langArray('mydonate_title1');?></span>
                                        <div class="dyk-stat-badge badge-emerald">
                                            <i data-lucide="heart"></i>
                                        </div>
                                    </div>
                                    <div class="dyk-stat-value">
                                        <?php echo $show_currency2; ?><?php echo number_format_currency($nominal_donasi_terkumpul); ?>
                                    </div>
                                    <div class="dyk-stat-subtext">
                                        <span><?php echo get_langArray('mydonate_desc1');?></span>
                                    </div>
                                </div>
                            </div>
                        </div>  
                        <div class="col-lg-4 col-jumlah-donasi">
                            <div class="card dyk-compact-stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="dyk-stat-label"><?php echo get_langArray('mydonate_title2');?></span>
                                        <div class="dyk-stat-badge badge-blue">
                                            <i data-lucide="trophy"></i>
                                        </div>
                                    </div>
                                    <div class="dyk-stat-value">
                                        <?php echo number_format_currency($jumlah_donasi_semua); ?>
                                    </div>
                                    <div class="dyk-stat-subtext">
                                        <span><strong style="color: #059669;"><?php echo number_format_currency($jumlah_donasi_terkumpul); ?></strong> <?php echo get_langArray('mydonate_desc2');?> <?php echo number_format_currency($jumlah_donasi_semua); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>    
                        <div class="col-lg-4 col-campaign-donasi">
                            <div class="card dyk-compact-stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="dyk-stat-label"><?php echo get_langArray('mydonate_title3');?></span>
                                        <div class="dyk-stat-badge badge-purple">
                                            <i data-lucide="radio"></i>
                                        </div>
                                    </div>
                                    <div class="dyk-stat-value">
                                        <?php echo count($data_donasi2); ?>
                                    </div>
                                    <div class="dyk-stat-subtext">
                                        <span><?php echo get_langArray('mydonate_desc3');?></span>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div><!--end row-->   
                    <div class="row">
                        <div class="col-12">
                            <div class="card" style="max-width: 100%;">
                                <div class="card-body">
                                    <h3 class="header-title mt-0">My Donate</h3> 
                                    <br>
                                    <br>
                                    <div class="table-responsive dash-social">

                                        <table id="datatable" class="table">
                                            <thead class="thead-light">
                                            <tr>
                                                <th>No</th>
                                                <th></th>
                                                <th><?php echo get_langArray('mydonate_label_col1');?></th>
                                                <th style="width: 110px;"><?php echo get_langArray('mydonate_label_col2');?></th>
                                                <th>Program</th>                                                 
                                                <th>Payment</th>                                   
                                                <th>Date</th>
                                                <th><?php echo get_langArray('mydonate_label_col3');?></th>
                                            </tr><!--end tr-->
                                            </thead>
        
                                            <tbody>
                                            <?php 
                                            $no = 1;
                                            foreach ($data_donasi as $row) { ?>
                                                <?php 

                                                    
                                                    
                                                    $row2 = $wpdb->get_results("SELECT user_pp_img FROM $table_name6 where user_id='$id_login' ")[0];

                                                    $row_3 = $wpdb->get_results("SELECT title, slug, publish_status FROM $table_name where campaign_id='$row->campaign_id' ");

                                                    if (!empty($row_3) && isset($row_3[0])) {
                                                        if ($row_3[0]->publish_status == '1') {
                                                            $campaign_url = get_site_url() . '/campaign/';
                                                        } else {
                                                            $campaign_url = get_site_url() . '/preview/';
                                                        }
                                                        $row3 = $row_3[0];
                                                        $slugnya = $row3->slug;
                                                        $titlenya = $row3->title;
                                                    } else {
                                                        // Handle case when no result is found
                                                        $campaign_url = ''; // or some default behavior
                                                        $row3 = [];
                                                        $slugnya = '';
                                                        $titlenya = '-';
                                                    }

                                                    $user_info = get_userdata($id_login);
                                                    if($user_info!=null){
                                                        $fullname = $user_info->first_name.' '.$user_info->last_name;
                                                    }

                                                    $time_donate = date('Y-m-d H:i:s',strtotime('+0 hour',strtotime($row->created_at)));

                                                    $readtime = new humanReadtime();
                                                    $a = $readtime->dyk_human_time($time_donate);

                                                    if($row->status=='1'){
                                                        $status = '<span class="active-status p_received">Success</span>';
                                                    }else{
                                                        $status = '<span class="active-status p_waiting">Waiting</span>';
                                                    }
                                                    

                                                ?>
                                                
                                                <tr id="donasi_<?php echo $row->id?>">
                                                    <td><?php echo $no; ?></td>
                                                    <td>
                                                        <?php if($row2->user_pp_img=='') { ?>
                                                        <img src="<?php echo plugin_dir_url( __FILE__ ) . "../assets/images/pp.svg"; ?>" alt="" class="rounded-circle thumb-md" style="border: 1px solid #dde4ec;height: 38px;width: 38px;margin-top: -5px;">
                                                        <?php }else{?>
                                                            <img src="<?php echo $row2->user_pp_img; ?>" alt="" class="rounded-circle thumb-md" style="border: 1px solid #dde4ec;height: 38px;width: 38px;margin-top: -5px;">
                                                        <?php } ?>
                                                    </td>
                                                    <td><?php echo $row->name; ?></td>
                                                    <td><?php echo $show_currency2; ?><?php echo number_format_currency($row->nominal); ?>
                                                    <br><a href="javascript:;" class="detail_donasi" data-id="<?php echo $row->id?>"><i data-lucide="file-box"></i><span><?php echo $row->invoice_id; ?><span></a>

                                                    </td>
                                                    <td><a href="<?php echo $campaign_url.$slugnya; ?>" target="_blank"><?php echo $titlenya; ?></a></td>
                                                    <td><?php echo $status; ?></td>
                                                    <td><?php echo date("M",strtotime($row->created_at)).'&nbsp;'.date("j",strtotime($row->created_at)).',&nbsp;'.date("Y",strtotime($row->created_at)).'&nbsp;-&nbsp;'.date("H:i ",strtotime($row->created_at)).'<br><span style='."'".'font-size:10px;color:#91a2b0;'."'".'>'.$a.'<span>'; ?></td>
                                                    <td style="text-align:left;">
                                                        <?php if($row->status=='1'){ ?>
                                                        <a <?php echo 'href="'.get_site_url().'/wp-admin/?donasiaja=print_kuitansi&inv='.$row->invoice_id.'"';?> target="_blank">
                                                        <button type="button" class="btn btn-outline-light btn-sm px-2">
                                                            <i data-lucide="download"></i>&nbsp;&nbsp;Download
                                                        </button>
                                                        </a>
                                                        <?php }else{ ?> 

                                                        <button type="button" class="btn btn-outline-light btn-sm px-2" style="color:#c4d0d9;cursor:default;">
                                                            <i data-lucide="download"></i>&nbsp;&nbsp;Download
                                                        </button>
                                                        <?php } ?>
                                                        

                                                        
                                                    </td>
                                                    
                                                </tr>
                                            <?php $no++; } ?>
                                            
                                                                                            
                                            </tbody>
                                        </table>                    
                                    </div>                                         
                                </div><!--end card-body--> 
                            </div><!--end card--> 
                        </div> <!--end col-->                               
                    </div><!--end row--> 

                </div><!-- container -->

            
            </div>
            <!-- end page content -->
        </div>
        <!-- end page-wrapper -->

        <div id="lala-alert-container"><div id="lala-alert-wrapper"></div></div>

        <style>
            #table_donasi {
                margin: 0 auto;
                margin-top: 10px;
                margin-bottom: 20px;
            }
            #table_donasi td {
                text-align: left;
                font-size: 15px;
                padding: 4px 7px;
            }
            .inv{
                font-size: 13px;
                background: #f1f5ff;
                padding: 12px 10px;
                margin-top: -16px;
                margin-bottom: -16px;
            }
            .title_donasi {
                font-size: 18px;
                padding: 0 20px;
            }
            .swal2-popup {
                padding-bottom: 40px !important;
                padding-top: 30px !important;
                border-radius: 12px;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            button.swal2-close {
                font-size: 28px;
                margin-top: 5px;
                margin-right: 5px;
            }
            .p_waiting {
                background:#E1345E;
            }
            .box_table {
                padding: 10px 20px;
            }
        </style>

        <!-- Required datatable js -->

        <script src="<?php echo plugin_dir_url( __FILE__ ); ?>plugins/datatables/jquery.dataTables.min.js"></script>
        <script src="<?php echo plugin_dir_url( __FILE__ ); ?>plugins/datatables/dataTables.bootstrap4.min.js"></script>
       
        <!-- sweetalert2 -->
        <link href="<?php echo plugin_dir_url( __FILE__ ); ?>plugins/sweet-alert2/sweetalert2.min.css" rel="stylesheet" type="text/css">
        <script src="<?php echo plugin_dir_url( __FILE__ ); ?>plugins/sweet-alert2/sweetalert2.min.js"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

        <script>

        jQuery(document).ready(function($){

            $('#datatable').DataTable();

            // Initialize the plugin
            $(document).on('click', '.detail_donasi', function(e){

                Swal.fire({
                      title: '<strong id="popup_title"><i data-lucide="file-text" style="width:18px;height:18px;color:#2563eb;margin-right:6px;vertical-align:-2px;"></i> Detail Donasi</strong>',
                      icon: false,
                      width: '780px',
                      html:
                        '<div id="data_box"><div class="dyk-modal-loading-box"><div class="dyk-loading-icon-wrapper"><i data-lucide="loader-2" class="dyk-lucide-spin"></i></div><div class="dyk-loading-text">Memuat Detail Donasi...</div><div class="dyk-loading-subtext">Mengambil rincian data transaksi</div></div></div>',
                      showCloseButton: true,
                      didOpen: function() {
                        if (window.lucide && typeof lucide.createIcons === "function") { lucide.createIcons(); }
                      },
                      showClass: {
                        popup: 'animate__animated animate__zoomIn animate__faster'
                      },
                      hideClass: {
                        popup: 'animate__animated animate__fadeOutUp animate__faster'
                      }
                    })

                var donasi_id = $(this).data('id');
                var data_nya = [
                    donasi_id
                ];

                var data = {
                    "action": "dykfunction_get_mydonasi",
                    "datanya": data_nya
                };
                
                jQuery.post(ajaxurl, data, function(response) {
                    $('#data_box').html(response);
                    if (window.lucide && typeof lucide.createIcons === "function") { lucide.createIcons(); }
                }).fail(function() {
                    $('#data_box').html('<div class="dyk-modal-error-box"><div class="dyk-error-icon-wrapper"><i data-lucide="alert-circle"></i></div><div class="dyk-loading-text">Gagal Memuat Data</div><div class="dyk-loading-subtext">Koneksi jaringan terputus. Silakan coba lagi.</div></div>');
                    if (window.lucide && typeof lucide.createIcons === "function") { lucide.createIcons(); }
                });
            });

        });

    </script>



    <?php
}