<?php

namespace CBSE\Admin\Settings;

use DateTime;

class MailCoachCbseSettings extends CbseSettings
{
    private string $sectionHeader = 'cbse_coach_mail';

    public function __construct()
    {
        parent::__construct('cbse_coach_mail', 'cbse_coach_mail_options');
    }

    /**
     * @inheritDoc
     */
    public function tabName(): string
    {
        return __('Mail - Coach', 'course_booking_system_extension');
    }

    /**
     * @inheritDoc
     */
    public function tabKey(): string
    {
        return 'mailcoach';
    }

    /**
     * @inheritDoc
     */
    public function registerSettings()
    {
        //section name, display name, callback to print description of section, page to which section is attached.
        add_settings_section($this->sectionHeader,
            __('Coach Mails', 'course_booking_system_extension'),
            [$this, 'sectionCoachMailText'],
            'course_booking_system_extension');

        //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
        //last field section is optional.
        add_settings_field('subject',
            __('Subject', 'course_booking_system_extension'),
            [$this, 'subject'],
            'course_booking_system_extension',
            $this->sectionHeader);
        add_settings_field('message',
            __('Message', 'course_booking_system_extension'),
            [$this, 'message'],
            'course_booking_system_extension',
            $this->sectionHeader);
        add_settings_field('cron_enable',
            __('Cron Enable', 'course_booking_system_extension'),
            [$this, 'cronEnable'],
            'course_booking_system_extension',
            $this->sectionHeader);
        add_settings_field('cron_before_time',
            __('Cron Sent before course', 'course_booking_system_extension'),
            [$this, 'cronBeforeTime'],
            'course_booking_system_extension',
            $this->sectionHeader);
    }

    public function sectionCoachMailText()
    {
        $text = '<p>';
        $text .= __('Here you can set all options for mail which are sent to the coaches.', 'course-booking-system-extension');
        $text .= '</p>';

        echo $text;
    }

    public function subject()
    {
        $value = esc_attr($this->getOptions('subject') ?? __('Sports operation documentation', 'course-booking-system-extension'));
        echo "<input id='subject' name='cbse_coach_mail_options[subject]' type='text' value='" . $value . "' />";
        echo "<p class='description'>" . __('Prefix of the subject', 'course-booking-system-extension') . "</p>";
    }

    public function message()
    {
        $value = esc_attr($this->getOptions('message') ?? "");
        $html = "<textarea  id='mail_coach_message' name='cbse_coach_mail_options[message]' type='text' row='6' cols='50'>" . $value . "</textarea>";
        $html .= "<ul class='description'>";
        $html .= "<li>{__('%first_name% will be replaced with the first name of the coach.', 'course-booking-system-extension')}</li>";
        $html .= "<li>{__('%last_name% will be replaced with the last name of the coach.', 'course-booking-system-extension')}</li>";
        $html .= "<li>{__('%course_date% will be replaced with the date of the course.', 'course-booking-system-extension')}</li>";
        $html .= "<li>{__('%course_start% will be replaced with the start time of the course.', 'course-booking-system-extension')}</li>";
        $html .= "<li>{__('%course_end% will be replaced with the end time of the course.', 'course-booking-system-extension')}</li>";
        $html .= "<li>{__('%course_title% will be replaced with the name of the course.', 'course-booking-system-extension') }</li>";
        $html .= "<li>{__('%number_of_bookings% will be replaced with number of bookings.', 'course-booking-system-extension') }</li>";
        $html .= "<li>{__('%maximum_participants% will be replaced with the maximum of participants in the course.', 'course-booking-system-extension')}</li>";
        $html .= "<li>{__('%booking_names% will be replaced with the names of the booking.', 'course-booking-system-extension') }</li>";
        $html .= '</ul>';

        echo $html;
    }

    public function cronEnable()
    {
        $value = esc_attr($this->getOptions('cron_enable') ?? 1);
        $html = '<input type="checkbox" id="cron_enable" name="cbse_coach_mail_options[cron_enable]" value="1"' . checked(1, $value, false) . '/>';
        $html .= '<label for="cron_enable">' . __('Sends the head of course a mail with the participants.', 'course-booking-system-extension') . '</label>';
        if ($this->cbse_cron_enabled())
        {
            $lastRun = get_option('cbse_cron_quarterly_last_run');
            $dateLastRun = new DateTime();
            $dateLastRun->setTimestamp($lastRun);
            $dateLastRun->setTimezone(wp_timezone());
            $html .= '<p>' . __('Cron is active.', 'course-booking-system-extension') . ' ' . sprintf(__('Last run was: %s %s', 'course-booking-system-extension'), $dateLastRun->format(get_option('date_format')), $dateLastRun->format(get_option('time_format'))) . '</p>';
        }

        echo $html;
    }

    private function cbse_cron_enabled(): bool
    {
        return (bool)wp_next_scheduled('cbse_cron_quarterly_hook');

    }

    public function cronBeforeTime()
    {
        $valueHours = esc_attr($this->getOptions('cron_before_time_hour') ?? 2);
        $valueMinutes = esc_attr($this->getOptions('cron_before_time_minute') ?? 0);
        echo "<input id='cron_before_time_hour' name='cbse_coach_mail_options[cron_before_time_hour]' type='number' min='0' max='23' value='" . $valueHours . "' />" . __('Hour', 'course-booking-system-extension');
        echo "<input id='cron_before_time_minute' name='cbse_coach_mail_options[cron_before_time_minute]' type='number' min='0' max='59' value='" . $valueMinutes . "' />" . __('Minute', 'course-booking-system-extension');
    }

    public function renderSettingsPage()
    {
        settings_fields($this->sectionHeader);
    }

    public function validate($input)
    {
        do_action('qm/debug', 'MailCoachCbseSettings->Validate {input}', ['input' => json_encode($input),]);
        return $input;
    }

}
