<?php

namespace CBSE\Admin\Settings;

use CBSE\Cron\DocumentationPrint;
use DateTime;

class AutoPrintCbseSettings extends CbseSettings
{
    private string $sectionHeader = 'cbse_auto_print';

    public function __construct()
    {
        parent::__construct('cbse_auto_print', 'cbse_auto_print_options');
    }

    /**
     * @inheritDoc
     */
    public function tabName(): string
    {
        return __('Auto Print', 'course_booking_system_extension');
    }

    /**
     * @inheritDoc
     */
    public function tabKey(): string
    {
        return 'autoprint';
    }

    /**
     * @inheritDoc
     */
    public function registerSettings()
    {
        //section name, display name, callback to print description of section, page to which section is attached.
        add_settings_section($this->sectionHeader, __('Auto Print', CBSE_LANGUAGE_DOMAIN), [$this, 'sectionAutoPrint'], 'course_booking_system_extension');

        //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
        //last field section is optional.
        add_settings_field('subject', __('Subject', CBSE_LANGUAGE_DOMAIN), [$this, 'subject'], 'course_booking_system_extension', $this->sectionHeader);
        add_settings_field('cron_enable', __('Cron Enable', CBSE_LANGUAGE_DOMAIN), [$this, 'cronEnable'], 'course_booking_system_extension', $this->sectionHeader);
        add_settings_field('cron_before_time', __('Cron Sent before course', CBSE_LANGUAGE_DOMAIN), [$this, 'cronBeforeTime'], 'course_booking_system_extension', $this->sectionHeader);
    }

    public function subject()
    {
        $value = esc_attr($this->getOptions('subject') ?? __('Auto print - Sports operation documentation', CBSE_LANGUAGE_DOMAIN));
        echo "<input id='subject' name='cbse_auto_print_options[subject]' type='text' value='" . $value . "' />";
        echo "<p class='description'>" . __('Prefix of the subject', CBSE_LANGUAGE_DOMAIN) . "</p>";
    }

    public function validateInput($input): array
    {
        do_action('qm/debug', 'AutoPrintCbseSettings->Validate {input}', ['input' => json_encode($input)]);

        $validatedInput['subject'] = $input['subject'];
        $validatedInput['cron_enable'] = isset($input['cron_enable']) ? 1 : 0;
        $validatedInput['cron_before_time_hour'] = $input['cron_before_time_hour'];
        $validatedInput['cron_before_time_minute'] = $input['cron_before_time_minute'];

        $cron = DocumentationPrint::getInstance();
        $cron->switch(boolval($validatedInput['cron_enable']));

        return $validatedInput;
    }

    public function sectionAutoPrint()
    {
        $text = '<p>';
        $text .= __('Here you can set all options for the auto print functionality.', CBSE_LANGUAGE_DOMAIN);
        $text .= '</p>';

        echo $text;
    }

    public function cronEnable()
    {
        $value = esc_attr($this->getOptions('cron_enable') ?? 1);
        $html = '<input type="checkbox" id="cron_enable" name="cbse_auto_print_options[cron_enable]" value="1"' . checked(1, $value, false) . '/>';
        $html .= '<label for="cron_enable">' . __('Sends the head of course a mail with the participants.', CBSE_LANGUAGE_DOMAIN) . '</label>';
        $cron = DocumentationPrint::getInstance();
        if ($cron->isActivated())
        {
            $dateLastRun = new DateTime();
            $dateLastRun->setTimestamp($cron->getLastRun());
            $dateLastRun->setTimezone(wp_timezone());
            $html .= '<p>' . __('Cron is active.', CBSE_LANGUAGE_DOMAIN) . ' ' . sprintf(__('Last run was: %s %s', CBSE_LANGUAGE_DOMAIN), $dateLastRun->format(get_option('date_format')), $dateLastRun->format(get_option('time_format'))) . '</p>';
        }

        echo $html;
    }

    public function cronBeforeTime()
    {
        $valueHours = esc_attr($this->getOptions('cron_before_time_hour') ?? 0);
        $valueMinutes = esc_attr($this->getOptions('cron_before_time_minute') ?? 20);
        echo "<input id='cron_before_time_hour' name='cbse_auto_print_options[cron_before_time_hour]' type='number' min='0' max='23' value='" . $valueHours . "' />" . __('Hour', CBSE_LANGUAGE_DOMAIN);
        echo "<input id='cron_before_time_minute' name='cbse_auto_print_options[cron_before_time_minute]' type='number' min='0' max='59' value='" . $valueMinutes . "' />" . __('Minute', CBSE_LANGUAGE_DOMAIN);
    }

    public function renderSettingsPage()
    {
        settings_fields($this->sectionHeader);
    }
}
