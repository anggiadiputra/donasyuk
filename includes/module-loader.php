<?php
if ( ! defined( 'ABSPATH' ) ) {
    die;
}

function dyk_load_domain_modules() {
    $modules = array(
        'auth.php',
        'campaign.php',
        'donation.php',
        'payment.php',
        'webhook.php',
    );

    foreach ( $modules as $module ) {
        $path = ROOTDIR_DYK . 'includes/modules/' . $module;
        if ( file_exists( $path ) ) {
            require_once $path;
        }
    }
}
