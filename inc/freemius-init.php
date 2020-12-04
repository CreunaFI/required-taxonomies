<?php

// Create a helper function for easy SDK access.
if ( !function_exists( 'vgrtt_fs' ) ) {
    function vgrtt_fs()
    {
        global  $vgrtt_fs ;
        
        if ( !isset( $vgrtt_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $vgrtt_fs = fs_dynamic_init( array(
                'id'             => '2512',
                'slug'           => 'required-taxonomies',
                'type'           => 'plugin',
                'public_key'     => 'pk_c20ea12e47f3c7bb7bdddb7953079',
                'is_premium'     => false,
                'anonymous_mode' => true,
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                'first-path' => 'admin.php?page=wprtt_welcome_page',
                'support'    => false,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $vgrtt_fs;
    }

}
// Init Freemius.
vgrtt_fs();
// Signal that SDK was initiated.
do_action( 'vgrtt_fs_loaded' );