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
        add_action('admin_menu', [$this, 'AddSettingsPageInMenu']);
        add_action('admin_init', [$this, 'RegisterSettings']);


        $this->generalCbseSettings = new GeneralCbseSettings();
        $this->pdfCbseSettings = new PdfCbseSettings();
        $this->mailCoachCbseSettings = new MailCoachCbseSettings();
        $this->autoPrintCbseSettings = new AutoPrintCbseSettings();
    }

    /**
     * Shows a menu item for the setting und the general settings tabs
     */
    public function AddSettingsPageInMenu()
    {
        add_options_page(__('Course Booking System Extension', CBSE_LANGUAGE_DOMAIN), //Page Title
            __('Course Booking System Extension', CBSE_LANGUAGE_DOMAIN), //Menu Title
            'manage_options', //Capability
            'course_booking_system_extension', //Page slug
            [$this, 'RenderSettingsPage']); //Callback to print html
    }


    public function RegisterSettings()
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

    public function RenderSettingsPage()
    {
        ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <?php
            settings_errors(); ?>
            <h1><?php
                echo esc_html(get_admin_page_title()); ?></h1>
            <h2 class="nav-tab-wrapper"><?php
                $this->SettingsTab(); ?></h2>

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

    public function SettingsTab()
    {
        $active_tab = $this->getActiveTab();

        echo $this->generalCbseSettings->getTabHtmlLink($active_tab);
        echo $this->pdfCbseSettings->getTabHtmlLink($active_tab);
        echo $this->mailCoachCbseSettings->getTabHtmlLink($active_tab);
        echo $this->autoPrintCbseSettings->getTabHtmlLink($active_tab);
    }


}

// TODO: Find a better WordPress way
$settings = new Settings();
