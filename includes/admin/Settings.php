<?php

namespace CBSE;

use DateTime;

class Settings
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'AddSettingsPageInMenu']);
        add_action('admin_init', [$this, 'RegisterSettings']);
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
                $this->SettingsSectionsFieldsPdf();
                break;
            case 'legacy':
                //section name, display name, callback to print description of section, page to which section is attached.
                add_settings_section('cbse_header', __('Header', 'course_booking_system_extension'), [$this, 'cbse_plugin_section_header_text'], 'course_booking_system_extension');

                //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
                //last field section is optional.
                add_settings_field('header_image_attachment_id', __('Image Attachment ID', 'course_booking_system_extension'), [$this, 'cbse_header_image_attachment_id'], 'course_booking_system_extension', 'cbse_header');
                add_settings_field('header_title', __('cbse_header_title', 'course_booking_system_extension'), [$this, 'cbse_header_title'], 'course_booking_system_extension', 'cbse_header');
                add_settings_field('mail_coach_message', __('Coach Mail Message', 'course_booking_system_extension'), [$this, 'cbse_mail_coach_message'], 'course_booking_system_extension', 'cbse_header');
                add_settings_field('mail_categories_title', __('Title for Categories', 'course_booking_system_extension'), [$this, 'cbse_mail_categories_title'], 'course_booking_system_extension', 'cbse_header');
                add_settings_field('mail_categories_exclude', __('Exclude Categories', 'course_booking_system_extension'), [$this, 'cbse_mail_categories_exclude'], 'course_booking_system_extension', 'cbse_header');
                add_settings_field('mail_tags_title', __('Title for Tags', 'course_booking_system_extension'), [$this, 'cbse_mail_tags_title'], 'course_booking_system_extension', 'cbse_header');
                add_settings_field('mail_tags_exclude', __('Exclude Tags', 'course_booking_system_extension'), [$this, 'cbse_mail_tags_exclude'], 'course_booking_system_extension', 'cbse_header');
                add_settings_field('cron_enable', __('Cron Enable', 'course_booking_system_extension'), [$this, 'cbse_cron_enable'], 'course_booking_system_extension', 'cbse_header');
                add_settings_field('cron_before_time', __('Cron Sent before course', 'course_booking_system_extension'), [$this, 'cbse_cron_before_time'], 'course_booking_system_extension', 'cbse_header');
                break;
        }

        //section name, form element name, callback for sanitization
        add_option('cbse_pdf_header_options', null);
        register_setting('cbse_pdf_header', 'cbse_pdf_header_options', [$this, 'PdfHeaderValidate']);
        register_setting('cbse_header', 'cbse_options', [$this, 'cbse_header_validate']); // Legacy
    }


    function cbse_initialize_setting()
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

    function cbse_missing_setting()
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
                        settings_fields('cbse_pdf_header');
                        break;
                    case 'legacy':
                        settings_fields('cbse_header');
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
           href="<?php echo admin_url('options-general.php?page=course_booking_system_extension&tab=mail'); ?>"><?php _e('Mail', 'course_booking_system_extension'); ?></a>
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

    /* ======== Start PDF ======== */
    private function SettingsSectionsFieldsPdf()
    {
        add_settings_section('cbse_pdf_header', __('Header of PDF', 'course_booking_system_extension'), [$this, 'SectionPdfHeaderText'], 'course_booking_system_extension');
    }

    public function SectionPdfHeaderText(){
        echo '<p>' . _e('Here you can set all header options for generated pdf.', 'course-booking-system-extension') . '</p>';
    }
    /* ======== End  PDF ======== */

    function cbse_plugin_section_header_text()
    {
        echo '<p>' . _e('Here you can set all the options for using the Course Booking System Extension', 'course-booking-system-extension') . '</p>';
    }

    /* Header Image */
    function cbse_header_image_attachment_id()
    {
        $options = get_option('cbse_options');
        echo "<input id='header_image_attachment_id' name='cbse_options[header_image_attachment_id]' type='text' value='" . esc_attr($options['header_image_attachment_id'] ?? "") . "' />";
    }

    /* Header Title */
    function cbse_header_title()
    {
        $options = get_option('cbse_options');
        echo "<input id='header_title' name='cbse_options[header_title]' type='text' value='" . esc_attr($options['header_title'] ?? "") . "' />";
    }

    /* Message to coach */
    function cbse_mail_coach_message()
    {
        $options = get_option('cbse_options');
        $html = "<textarea  id='mail_coach_message' name='cbse_options[mail_coach_message]' type='text' row='6' cols='50'>" . esc_attr($options['mail_coach_message'] ?? "") . "</textarea>";
        $html .= "<ul class='description'>";
        $html .= '<li>' . __('%first_name% will be replaced with the first name of the coach.', 'course-booking-system-extension') . '</li>';
        $html .= '<li>' . __('%last_name% will be replaced with the last name of the coach.', 'course-booking-system-extension') . '</li>';
        $html .= '<li>' . __('%course_date% will be replaced with the date of the course.', 'course-booking-system-extension') . '</li>';
        $html .= '<li>' . __('%course_start% will be replaced with the start time of the course.', 'course-booking-system-extension') . '</li>';
        $html .= '<li>' . __('%course_end% will be replaced with the end time of the course.', 'course-booking-system-extension') . '</li>';
        $html .= '<li>' . __('%course_title% will be replaced with the name of the course.', 'course-booking-system-extension') . '</li>';
        $html .= '<li>' . __('%number_of_bookings% will be replaced with number of bookings.', 'course-booking-system-extension') . '</li>';
        $html .= '<li>' . __('%maximum_participants% will be replaced with the maximum of participants in the course.', 'course-booking-system-extension') . '</li>';
        $html .= '<li>' . __('%booking_names% will be replaced with the names of the booking.', 'course-booking-system-extension') . '</li>';
        $html .= '</ul>';

        echo $html;
    }

    /* Categoeries */
    function cbse_mail_categories_title()
    {
        $options = get_option('cbse_options');
        echo "<input id='mail_categories_title' name='cbse_options[mail_categories_title]' type='text' value='" . esc_attr($options['mail_categories_title'] ?? "") . "' />";
    }

    function cbse_mail_categories_exclude()
    {
        $options = get_option('cbse_options');
        echo "<input id='mail_categories_exclude' name='cbse_options[mail_categories_exclude]' type='text' value='" . esc_attr($options['mail_categories_exclude'] ?? "") . "' />";
        echo "<p class='description'>" . __('0 will hide this field. Please add the values comma seperated.', 'course-booking-system-extension') . "</p>";
    }

    /* Categoeries */
    function cbse_mail_tags_title()
    {
        $options = get_option('cbse_options');
        echo "<input id='mail_tags_title' name='cbse_options[mail_tags_title]' type='text' value='" . esc_attr($options['mail_tags_title'] ?? "") . "' />";
    }

    function cbse_mail_tags_exclude()
    {
        $options = get_option('cbse_options');
        echo "<input id='mail_tags_exclude' name='cbse_options[mail_tags_exclude]' type='text' value='" . esc_attr($options['mail_tags_exclude'] ?? "") . "' />";
        echo "<p class='description'>" . __('0 will hide this field. Please add the values comma seperated.', 'course-booking-system-extension') . "</p>";
    }

    function cbse_cron_enable()
    {
        $options = get_option('cbse_options');
        $html = '<input type="checkbox" id="cron_enable" name="cbse_options[cron_enable]" value="1"' . checked(1, $options['cron_enable'], false) . '/>';
        $html .= '<label for="cron_enable">' . __('Sends the head of course a mail with the participants.', 'course-booking-system-extension') . '</label>';
        if ($this->cbse_cron_enabled()) {
            $lastRun = get_option('cbse_cron_quarterly_last_run');
            $dateLastRun = new DateTime();
            $dateLastRun->setTimestamp($lastRun);
            $dateLastRun->setTimezone(wp_timezone());
            $html .= '<p>' . __('Cron is active.', 'course-booking-system-extension') . ' ' . sprintf(__('Last run was: %s %s', 'course-booking-system-extension'), $dateLastRun->format(get_option('date_format')), $dateLastRun->format(get_option('time_format'))) . '</p>';
        }

        echo $html;
    }

    function cbse_cron_before_time()
    {
        $options = get_option('cbse_options');
        echo "<input id='cron_before_time_hour' name='cbse_options[cron_before_time_hour]' type='number' min='0' max='23' value='" . esc_attr($options['cron_before_time_hour'] ?? "") . "' />" . __('Hour', 'course-booking-system-extension');
        echo "<input id='cron_before_time_minute' name='cbse_options[cron_before_time_minute]' type='number' min='0' max='59' value='" . esc_attr($options['cron_before_time_minute'] ?? "") . "' />" . __('Minute', 'course-booking-system-extension');
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

    function cbse_cron_enabled(): bool
    {
        return (bool)wp_next_scheduled('cbse_cron_quarterly_hook');

    }


}

// TODO: Find a better WordPress way
$settings = new Settings();
