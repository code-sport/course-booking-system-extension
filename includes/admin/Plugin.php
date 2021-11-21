<?php

namespace CBSE\Admin;

class Plugin
{
    public function __construct()
    {
        // Plugin overview Page
        add_filter('plugin_action_links_' . PLUGIN_BASENAME, [$this, 'actionLinks']);
    }

    public function actionLinks($actions): array
    {
        $cbseLinks = array('<a href="' . admin_url('options-general.php?page=course_booking_system_extension') . '">' . __('Settings', 'course_booking_system_extension') . '</a>',);
        return array_merge($cbseLinks, $actions);
    }

}

$plugin = new Plugin();
