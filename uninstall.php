<?php
/**
 * Is running at uninstallation of this plugin
 *
 * Documentation: https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/
 */

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$option_name = 'cbse_options';

delete_option($option_name);

// for site options in Multisite
delete_site_option($option_name);

