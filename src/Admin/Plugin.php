<?php

namespace CBSE\Admin;

class Plugin
{
    public function __construct()
    {
        // Plugin overview Page
        add_filter('plugin_action_links_' . CBSE_PLUGIN_BASENAME, [$this, 'actionLinks']);
    }

    public function actionLinks($actions): array
    {
        $cbseLinks = array(
            $this->getSettings(),
        );
        return array_merge($cbseLinks, $actions);
    }

    /**
     * @return string
     */
    private function getSettings(): string
    {
        return '<a href="' . admin_url('options-general.php?page=course_booking_system_extension') . '">'
            . __('Settings', CBSE_LANGUAGE_DOMAIN) . '</a>';
    }

}
