<?php

namespace CBSE\Admin\Settings;

use DateTime;

class LegacyCbseSettings extends CbseSettings
{
    public function TabName(): string
    {
        return __('Legacy', 'course_booking_system_extension');
    }

    public function TabKey(): string
    {
        return 'legacy';
    }


    public function RegisterSettings()
    {
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
    }

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

    public function RenderSettingsPage()
    {
        settings_fields('cbse_header');
    }

    private function cbse_cron_enabled(): bool
    {
        return (bool)wp_next_scheduled('cbse_cron_quarterly_hook');

    }
}
