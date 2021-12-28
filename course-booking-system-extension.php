<?php
/**
 * Plugin Name: Course Booking System Extension
 * Plugin URI: https://github.com/code-sport/course-booking-system-extension
 * Description: Extension for Course Booking System
 * Version: 0.3.2
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

use CBSE\Admin\User\Admin\Plugin;
use CBSE\Admin\User\Admin\Settings;
use CBSE\Admin\User\Admin\User\User\UserCovid19StatusOverview;
use CBSE\Admin\User\Admin\User\UserCovid19StatusSettings;
use CBSE\Admin\User\Api\Api;
use CBSE\Admin\User\Cron\DocumentationCoach;
use CBSE\Admin\User\Cron\DocumentationPrint;
use CBSE\Admin\User\Shortcode\ShortcodeOverviewForCourseHead;
use CBSE\Admin\User\Shortcode\ShortcodeUserCovid19Status;
use CBSE\Admin\User\UserInformMethod;
use CBSE\Ajax;
use CBSE\TemplatesManager;

defined('ABSPATH') || exit;
define('CBSE_PLUGIN_BASENAME', plugin_basename(__FILE__));
const CBSE_LANGUAGE_DOMAIN = 'course-booking-system-extension';

CBSE\Logging::init(__FILE__);


$ajax = new Ajax();

ShortcodeOverviewForCourseHead::getInstance();
ShortcodeUserCovid19Status::getInstance();

DocumentationCoach::getInstance();
DocumentationPrint::getInstance();
Api::getInstance();

$templates = new TemplatesManager();
$templates->init();

if (is_admin())
{ // admin actions
    $plugin = new Plugin();
    $settings = new Settings();
    $userCovid = new UserCovid19StatusSettings();
    $userInfo = new UserInformMethod();
    UserCovid19StatusOverview::getInstance();
}

// Install and update
//add_action('upgrader_process_complete', 'cbse_install_and_update', 10, 2);
//register_activation_hook(CBSE_PLUGIN_BASENAME, 'cbse_install_and_update');

//register_activation_hook(CBSE_PLUGIN_BASENAME, 'cbse_cron_activation');
//add_action('upgrader_process_complete', 'cbse_cron_activation', 10, 2);

// Uninstall
register_deactivation_hook(CBSE_PLUGIN_BASENAME, 'cbse_cron_deactivate');
