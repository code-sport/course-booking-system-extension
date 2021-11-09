<?php

namespace CBSE\Admin;

use CBSE\Admin\Settings\LegacyCbseSettings;
use CBSE\Admin\Settings\PdfCbseSettings;

require_once plugin_dir_path(__FILE__) . 'Settings/CbseSettings.php';
require_once plugin_dir_path(__FILE__) . 'Settings/PdfSettings.php';
require_once plugin_dir_path(__FILE__) . 'Settings/LegacySettings.php';

class Settings
{
    private PdfCbseSettings $pdfSettings;
    private LegacyCbseSettings $legacySettings;

    public function __construct()
    {
        add_action('admin_menu', [$this, 'AddSettingsPageInMenu']);
        add_action('admin_init', [$this, 'RegisterSettings']);

        $this->pdfSettings = new PdfCbseSettings();
        $this->legacySettings = new LegacyCbseSettings();
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
            case 'pdf':
                $this->pdfSettings->registerSettings();
                break;
            case 'legacy':
                $this->legacySettings->registerSettings();
                break;
        }

        //section name, form element name, callback for sanitization
        add_option('cbse_pdf_header_options', null);
        register_setting('cbse_pdf_header', 'cbse_pdf_header_options', [$this, 'PdfHeaderValidate']);
        register_setting('cbse_header', 'cbse_options', [$this, 'cbse_header_validate']); // Legacy
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
                    case 'pdf':
                        $this->pdfSettings->renderSettingsPage();
                        break;
                    case 'legacy':
                        $this->legacySettings->renderSettingsPage();
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
        ?>
        <a class="nav-tab <?php echo $active_tab == 'general' || '' ? 'nav-tab-active' : ''; ?>"
           href="<?php echo admin_url('options-general.php?page=course_booking_system_extension&tab=general'); ?>"><?php _e('General', 'course_booking_system_extension'); ?></a>
        <a class="nav-tab <?php echo $active_tab == 'pdf' || '' ? 'nav-tab-active' : ''; ?>"
           href="<?php echo admin_url('options-general.php?page=course_booking_system_extension&tab=pdf'); ?>"><?php _e('PDF', 'course_booking_system_extension'); ?></a>
        <a class="nav-tab <?php echo $active_tab == 'mail' || '' ? 'nav-tab-active' : ''; ?>"
           href="<?php echo admin_url('options-general.php?page=course_booking_system_extension&tab=mail'); ?>"><?php _e('Mail - Coach', 'course_booking_system_extension'); ?></a>
        <a class="nav-tab <?php echo $active_tab == 'autoprint' || '' ? 'nav-tab-active' : ''; ?>"
           href="<?php echo admin_url('options-general.php?page=course_booking_system_extension&tab=autoprint'); ?>"><?php _e('Auto Print via Mail', 'course_booking_system_extension'); ?></a>
        <a class="nav-tab <?php echo $active_tab == 'legacy' || '' ? 'nav-tab-active' : ''; ?>"
           href="<?php echo admin_url('options-general.php?page=course_booking_system_extension&tab=legacy'); ?>"><?php _e('Legacy', 'course_booking_system_extension'); ?></a>
        <?php
    }

    private function getActiveTab()
    {
        return $_GET['tab'] ?? 'general';
    }

    /**
     * Validate the input for the header data
     * @param $input
     * @return array
     */
    function cbse_header_validate($input): array
    {
        // Header Image
        $validatedInput['header_image_attachment_id'] = trim($input['header_image_attachment_id']);
        if (!is_numeric($validatedInput['header_image_attachment_id'])) {
            $validatedInput['header_image_attachment_id'] = '';
        }

        $validatedInput['header_title'] = trim($input['header_title']);
        $validatedInput['mail_coach_message'] = trim($input['mail_coach_message']);
        $validatedInput['mail_categories_title'] = trim($input['mail_categories_title']);
        $validatedInput['mail_categories_exclude'] = trim($input['mail_categories_exclude']);
        $validatedInput['mail_tags_title'] = trim($input['mail_tags_title']);
        $validatedInput['mail_tags_exclude'] = trim($input['mail_tags_exclude']);
        $validatedInput['cron_enable'] = isset($input['cron_enable']) ? 1 : 0;
        $validatedInput['cron_before_time_hour'] = is_numeric(trim($input['cron_before_time_hour'])) ? trim($input['cron_before_time_hour']) : 2;
        $validatedInput['cron_before_time_minute'] = is_numeric(trim($input['cron_before_time_minute'])) ? trim($input['cron_before_time_minute']) : 0;

        $this->cbse_switch_cron(boolval($validatedInput['cron_enable']));

        return $validatedInput;
    }

    function cbse_switch_cron(bool $cronEnabled)
    {
        $hook = 'cbse_cron_quarterly_hook';

        if ($cronEnabled) {
            if (!wp_next_scheduled($hook)) {
                wp_schedule_event(time(), 'quarterly', $hook);
            }
        } else {
            $timestamp = wp_next_scheduled($hook);
            wp_unschedule_event($timestamp, $hook);
        }
    }


}

// TODO: Find a better WordPress way
$settings = new Settings();
