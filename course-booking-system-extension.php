<?php
/**
 * Plugin Name: Course Booking System Extension
 * Plugin URI: https://github.com/code-sport/course-booking-system-extension
 * Description: Extension for Course Booking System
 * Version: 0.1.1
 * Author: Code.Sport
 * Author URI: https://github.com/code-sport/
 * Text Domain: course-booking-system-extension
 * WC requires at least: 5.8.1
 * WC tested up to: 5.8.2
 * Requires PHP: 7.4
 * License: GPL v3
 * License URI: https://github.com/code-sport/course-booking-system-extension/blob/main/license.txt
 */

defined('ABSPATH') || exit;
define('PLUGIN_BASENAME', plugin_basename(__FILE__));


function cbse_include_all()
{
    require_once plugin_dir_path(__FILE__) . '../course-booking-system/includes/functions.php';
    require_once plugin_dir_path(__FILE__) . 'includes/Helper/ArrayHelper.php';
    require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
    require_once plugin_dir_path(__FILE__) . 'includes/templates.php';
    require_once plugin_dir_path(__FILE__) . 'includes/UserCovid19Status.php';
    require_once plugin_dir_path(__FILE__) . 'includes/dto/CourseInfoDate.php';


    // WordPress parts
    require_once plugin_dir_path(__FILE__) . 'includes/Ajax.php';
    require_once plugin_dir_path(__FILE__) . 'includes/api.php';
    require_once plugin_dir_path(__FILE__) . 'includes/cron.php';
    require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/ShortcodeOverviewForCourseHead.php';
    require_once plugin_dir_path(__FILE__) . 'includes/shortcodes/ShortcodeUserCovid19Status.php';

    // User
    require_once plugin_dir_path(__FILE__) . 'includes/admin/UserCovid19StatusSettings.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin/UserInformMethod.php';

}

function cbse_include_admin()
{
    require_once plugin_dir_path(__FILE__) . 'includes/admin/Settings.php';
    require_once plugin_dir_path(__FILE__) . 'includes/admin/Plugin.php';
}

cbse_include_all();

if (is_admin())
{ // admin actions
    cbse_include_admin();
}

// Install and update
add_action('upgrader_process_complete', 'cbse_install_and_update', 10, 2);
register_activation_hook(__FILE__, 'cbse_install_and_update');

register_activation_hook(__FILE__, 'cbse_cron_activation');
add_action('upgrader_process_complete', 'cbse_cron_activation', 10, 2);

// Uninstall
register_deactivation_hook(__FILE__, 'cbse_cron_deactivate');
