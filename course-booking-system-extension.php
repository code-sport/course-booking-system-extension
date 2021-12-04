<?php
/**
 * Plugin Name: Course Booking System Extension
 * Plugin URI: https://github.com/code-sport/course-booking-system-extension
 * Description: Extension for Course Booking System
 * Version: 0.2.0
 * Author: Code.Sport
 * Author URI: https://github.com/code-sport/
 * Text Domain: course-booking-system-extension
 * WC requires at least: 5.8.1
 * WC tested up to: 5.8.2
 * Requires PHP: 7.4
 * License: GPL v3
 * License URI: https://github.com/code-sport/course-booking-system-extension/blob/main/license.txt
 */

require_once((plugin_dir_path(__FILE__)) . '/vendor/autoload.php');

//require_once plugin_dir_path(__FILE__) . '../course-booking-system/includes/functions.php';

use CBSE\Admin\Plugin;
use CBSE\Admin\Settings;
use CBSE\Admin\UserCovid19StatusSettings;
use CBSE\Ajax;
use CBSE\Cron\DocumentationCoach;
use CBSE\Cron\DocumentationPrint;
use CBSE\Shortcode\ShortcodeOverviewForCourseHead;
use CBSE\Shortcode\ShortcodeUserCovid19Status;
use CBSE\TemplatesManager;
use CBSE\UserInformMethod;

defined('ABSPATH') || exit;
define('CBSE_PLUGIN_BASENAME', plugin_basename(__FILE__));
const CBSE_LANGUAGE_DOMAIN = 'course-booking-system-extension';


$ajax = new Ajax();

ShortcodeOverviewForCourseHead::getInstance();
ShortcodeUserCovid19Status::getInstance();

DocumentationCoach::getInstance();
DocumentationPrint::getInstance();

$templates = new TemplatesManager();
$templates->init();

if (is_admin())
{ // admin actions
    $plugin = new Plugin();
    $settings = new Settings();
    $userCovid = new UserCovid19StatusSettings();
    $userInfo = new UserInformMethod();
}

// Install and update
//add_action('upgrader_process_complete', 'cbse_install_and_update', 10, 2);
//register_activation_hook(CBSE_PLUGIN_BASENAME, 'cbse_install_and_update');

//register_activation_hook(CBSE_PLUGIN_BASENAME, 'cbse_cron_activation');
//add_action('upgrader_process_complete', 'cbse_cron_activation', 10, 2);

// Uninstall
register_deactivation_hook(CBSE_PLUGIN_BASENAME, 'cbse_cron_deactivate');
