<?php
/**
 * Plugin Name: Course Booking System Extension
 * Plugin URI: https://codesport.info/
 * Description: Extension for Course Booking System
 * Version: 1.0.0
 * Author: CodeSport
 * Author URI: https://codesport.info/
 * Text Domain: course-booking-system
 * WC requires at least: 5.7.2
 * WC tested up to: 5.7.2
 */

defined('ABSPATH') || exit;


function include_all()
{
    require_once plugin_dir_path(__FILE__) . 'includes/api.php';
}

function include_admin()
{
    require_once plugin_dir_path(__FILE__) . 'includes/admin/settings.php';
}

include_all();

if (is_admin()) { // admin actions
    include_admin();
}
