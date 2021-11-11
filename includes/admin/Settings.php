<?php

namespace CBSE\Admin;

use CBSE\Admin\Settings\AutoPrintCbseSettings;
use CBSE\Admin\Settings\GeneralCbseSettings;
use CBSE\Admin\Settings\LegacyCbseSettings;
use CBSE\Admin\Settings\MailCoachCbseSettings;
use CBSE\Admin\Settings\PdfCbseSettings;

require_once plugin_dir_path(__FILE__) . 'Settings/CbseSettings.php';
require_once plugin_dir_path(__FILE__) . 'Settings/GeneralCbseSettings.php';
require_once plugin_dir_path(__FILE__) . 'Settings/PdfCbseSettings.php';
require_once plugin_dir_path(__FILE__) . 'Settings/MailCoachCbseSettings.php';
require_once plugin_dir_path(__FILE__) . 'Settings/AutoPrintCbseSettings.php';
require_once plugin_dir_path(__FILE__) . 'Settings/LegacyCbseSettings.php';

class Settings
{
    private GeneralCbseSettings $generalCbseSettings;
    private PdfCbseSettings $pdfCbseSettings;
    private MailCoachCbseSettings $mailCoachCbseSettings;
    private AutoPrintCbseSettings $autoPrintCbseSettings;
    private LegacyCbseSettings $legacyCbseSettings;

    public function __construct()
    {
        add_action('admin_menu', [$this, 'AddSettingsPageInMenu']);
        add_action('admin_init', [$this, 'RegisterSettings']);


        $this->generalCbseSettings = new GeneralCbseSettings();
        $this->pdfCbseSettings = new PdfCbseSettings();
        $this->mailCoachCbseSettings = new MailCoachCbseSettings();
        $this->autoPrintCbseSettings = new AutoPrintCbseSettings();
        $this->legacyCbseSettings = new LegacyCbseSettings();

    }

    /**
     * Shows a menu item for the setting und the general settings tabs
     */
    public function AddSettingsPageInMenu()
    {
        add_options_page(
            __('Course Booking System Extension', 'course_booking_system_extension'), //Page Title
            __('Course Booking System Extension', 'course_booking_system_extension'), //Menu Title
            'manage_options', //Capability
            'course_booking_system_extension', //Page slug
            [$this, 'RenderSettingsPage']); //Callback to print html
    }


    public function RegisterSettings()
    {
        if (false === get_option('cbse_options')) {
            $this->cbse_initialize_setting();
        } else {
            $this->cbse_missing_setting();
        }

        switch ($this->getActiveTab()) {
            default:
            case $this->generalCbseSettings->tabKey():
                $this->generalCbseSettings->registerSettings();
                break;
            case $this->pdfCbseSettings->tabKey():
                $this->pdfCbseSettings->registerSettings();
                break;
            case $this->mailCoachCbseSettings->tabKey():
                $this->mailCoachCbseSettings->registerSettings();
                break;
            case $this->autoPrintCbseSettings->tabKey():
                $this->autoPrintCbseSettings->registerSettings();
                break;
            case $this->legacyCbseSettings->tabKey():
                $this->legacyCbseSettings->registerSettings();
                break;
        }
    }


    private function cbse_initialize_setting()
    {
        $settings = [
            'header_image_attachment_id' => '',
            'header_title' => __('Sports operation documentation', 'course-booking-system-extension'),
            'mail_coach_message' => __("Hi %first_name%,\n\nplease note the file in the attachment.\n\nRegards\nYour IT.", 'course-booking-system-extension'),
            'mail_categories_title' => __('Categories', 'course-booking-system-extension'),
            'mail_categories_exclude' => '',
            'mail_tags_title' => __('Tags', 'course-booking-system-extension'),
            'mail_tags_exclude' => '',
            'cron_enable' => 'true',
            'cron_before_time_hour' => 2,
            'cron_before_time_minute' => 0
        ];
        add_option('cbse_options', $settings);
    }

    private function cbse_missing_setting()
    {
        $options = get_option('cbse_options');

        if (!array_key_exists('header_title', $options)) {
            $options['header_title'] = __('Sports operation documentation', 'course-booking-system-extension');
        }

        if (!array_key_exists('mail_coach_message', $options)) {
            $options['mail_coach_message'] = __("Hi %first_name%,\n\nplease note the file in the attachment.\n\nRegards\nYour IT.", 'course-booking-system-extension');
        }

        if (!array_key_exists('mail_categories_title', $options)) {
            $options['mail_categories_title'] = __('Categories');
        }

        if (!array_key_exists('mail_tags_title', $options)) {
            $options['mail_tags_title'] = __('Tags');
        }

        if (!array_key_exists('cron_enable', $options)) {
            $options['cron_enable'] = 1;
        }

        if (!array_key_exists('cron_before_time_hour', $options)) {
            $options['cron_before_time_hour'] = 2;
        }

        if (!array_key_exists('cron_before_time_minute', $options)) {
            $options['cron_before_time_minute'] = 0;
        }

        update_option('cbse_options', $options);
    }

    public function RenderSettingsPage()
    {
        ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <?php settings_errors(); ?>
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <h2 class="nav-tab-wrapper"><?php $this->SettingsTab(); ?></h2>

            <form action="options.php" method="post">
                <?php
                //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
                switch ($this->getActiveTab()) {
                    default:
                    case $this->generalCbseSettings->tabKey():
                        $this->generalCbseSettings->renderSettingsPage();
                        break;
                    case $this->pdfCbseSettings->tabKey():
                        $this->pdfCbseSettings->renderSettingsPage();
                        break;
                    case $this->mailCoachCbseSettings->tabKey():
                        $this->mailCoachCbseSettings->renderSettingsPage();
                        break;
                    case $this->autoPrintCbseSettings->tabKey():
                        $this->autoPrintCbseSettings->renderSettingsPage();
                        break;
                    case $this->legacyCbseSettings->tabKey():
                        $this->legacyCbseSettings->renderSettingsPage();
                        break;
                }

                //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
                do_settings_sections('course_booking_system_extension');

                // Add the submit button to serialize the options
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function SettingsTab()
    {
        $active_tab = $this->getActiveTab();

        echo $this->generalCbseSettings->getTabHtmlLink($active_tab);
        echo $this->pdfCbseSettings->getTabHtmlLink($active_tab);
        echo $this->mailCoachCbseSettings->getTabHtmlLink($active_tab);
        echo $this->autoPrintCbseSettings->getTabHtmlLink($active_tab);
        echo $this->legacyCbseSettings->getTabHtmlLink($active_tab);
    }

    private function getActiveTab()
    {
        return $_GET['tab'] ?? 'general';
    }




}

// TODO: Find a better WordPress way
$settings = new Settings();
