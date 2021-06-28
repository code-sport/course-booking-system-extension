<?php
/**
 * Plugin Name: Course Booking System Extension
 * Plugin URI: https://codesport.info/
 * Description: Extension for Course Booking System
 * Version: 0.0.3
 * Author: CodeSport
 * Author URI: https://codesport.info/
 * Text Domain: course-booking-system
 * WC requires at least: 5.7.2
 * WC tested up to: 5.7.2
 */

defined('ABSPATH') || exit;


function include_all()
{
    require_once plugin_dir_path( __FILE__ ) . '../course-booking-system/includes/functions.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';

    // WordPress parts
    require_once plugin_dir_path(__FILE__) . 'includes/ajax.php';
    require_once plugin_dir_path(__FILE__) . 'includes/api.php';
    require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
}

function include_admin()
{
    require_once plugin_dir_path(__FILE__) . 'includes/admin/settings.php';
}

include_all();

if (is_admin()) { // admin actions
    include_admin();
}

// Install and update
add_action( 'upgrader_process_complete', 'cbse_install_and_update', 10, 2 );
register_activation_hook( __FILE__, 'cbse_install_and_update' );
