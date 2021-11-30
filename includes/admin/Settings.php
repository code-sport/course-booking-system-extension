<?php

namespace CBSE\Admin;

use CBSE\Admin\Settings\AutoPrintCbseSettings;
use CBSE\Admin\Settings\GeneralCbseSettings;
use CBSE\Admin\Settings\MailCoachCbseSettings;
use CBSE\Admin\Settings\PdfCbseSettings;

require_once plugin_dir_path(__FILE__) . 'Settings/CbseSettings.php';
require_once plugin_dir_path(__FILE__) . 'Settings/GeneralCbseSettings.php';
require_once plugin_dir_path(__FILE__) . 'Settings/PdfCbseSettings.php';
require_once plugin_dir_path(__FILE__) . 'Settings/MailCoachCbseSettings.php';
require_once plugin_dir_path(__FILE__) . 'Settings/AutoPrintCbseSettings.php';

class Settings
{
    private GeneralCbseSettings $generalCbseSettings;
    private PdfCbseSettings $pdfCbseSettings;
    private MailCoachCbseSettings $mailCoachCbseSettings;
    private AutoPrintCbseSettings $autoPrintCbseSettings;

    public function __construct()
    {
        add_action('admin_menu', [$this, 'addSettingsPageInMenu']);
        add_action('admin_init', [$this, 'registerSettings']);


        $this->generalCbseSettings = new GeneralCbseSettings();
        $this->pdfCbseSettings = new PdfCbseSettings();
        $this->mailCoachCbseSettings = new MailCoachCbseSettings();
        $this->autoPrintCbseSettings = new AutoPrintCbseSettings();
    }

    /**
     * Shows a menu item for the setting und the general settings tabs
     */
    public function addSettingsPageInMenu()
    {
        add_options_page(
            //Page Title
            __('Course Booking System Extension', CBSE_LANGUAGE_DOMAIN),
            //Menu Title
            __('Course Booking System Extension', CBSE_LANGUAGE_DOMAIN),
            //Capability
            'manage_options',
            //Page slug
            'course_booking_system_extension',
            //Callback to print html
            [$this, 'renderSettingsPage']);
    }


    public function registerSettings()
    {
        switch ($this->getActiveTab())
        {
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
        }
    }

    private function getActiveTab()
    {
        return $_GET['tab'] ?? 'general';
    }

    public function renderSettingsPage()
    {
        ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <?php
            settings_errors(); ?>
            <h1><?php
                echo esc_html(get_admin_page_title()); ?></h1>
            <h2 class="nav-tab-wrapper"><?php
                $this->settingsTab(); ?></h2>

            <form action="options.php" method="post">
                <?php
                //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
                switch ($this->getActiveTab())
                {
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

    public function settingsTab()
    {
        $activeTab = $this->getActiveTab();

        echo $this->generalCbseSettings->getTabHtmlLink($activeTab);
        echo $this->pdfCbseSettings->getTabHtmlLink($activeTab);
        echo $this->mailCoachCbseSettings->getTabHtmlLink($activeTab);
        echo $this->autoPrintCbseSettings->getTabHtmlLink($activeTab);
    }


}

// TODO: Find a better WordPress way
$settings = new Settings();
