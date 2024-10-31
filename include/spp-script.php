<?php
/**
 * Script Class
 * Handles the script and style functionality of plugin
 * @package Selectable Post and Page
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Spp_Script {
    function __construct() {
        // Action to add style in backend
        add_action( 'admin_enqueue_scripts', array( $this, 'wp_spp_admin_style' ) );
        //Action to add script at admin side
        add_action( 'admin_enqueue_scripts', array( $this, 'wp_spp_admin_script') );
        
        //Action to add script at user side 
        add_action( 'wp_enqueue_scripts', array( $this, 'wp_spp_enqueue_scripts' ), 5 );
    }
    
    /*
     * function to add style at admin side
     * @package Selectable Post and Page
     * @since 1.0.0 
     */
    function wp_spp_admin_style(){
        // Registring admin script
        wp_register_style( 'wp-spp-admin-style', WP_SPP_URL.'assets/css/wp-spp-admin.css', null, WP_SPP_VERSION );
        wp_enqueue_style( 'wp-spp-admin-style' );
    }
    
    /**
     * function to add script at admin side
     * @package Selectable Post and Page
     * @since 1.0.0
     */
    function wp_spp_admin_script() {
        // Registring admin script
        wp_register_script( 'wp-spp-admin-script', WP_SPP_URL.'assets/js/wp-spp-admin.js', array('jquery'), WP_SPP_VERSION, true );
        wp_enqueue_script( 'wp-spp-admin-script' );
    }
    
    
    /*
     * function to add style at theme side
     * @package Selectable Post and Page
     * @since 1.0.0
     */
    function wp_spp_enqueue_scripts() {
	if( is_active_widget( false, false, 'selectable_post_and_page' ) || is_customize_preview() ) {
            // Registring user custom css
            wp_register_style( 'wp-spp-custom-style', WP_SPP_URL . 'assets/css/wp-spp-custom.css', null, WP_SPP_VERSION );
            wp_enqueue_style( 'wp-spp-custom-style' );
	}
    }
}

$spp_script = new Spp_Script();