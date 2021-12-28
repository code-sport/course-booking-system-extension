<?php

namespace CBSE\Admin\User\Admin\User\User\Settings;

use CBSE\Admin\User\Cron\DocumentationPrint;
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
        return __('Auto Print via Mail', 'course_booking_system_extension');
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
        $this->registerSettingsForTags();
        add_settings_field('subject', __('Subject', CBSE_LANGUAGE_DOMAIN), [$this, 'subject'], 'course_booking_system_extension', $this->sectionHeader);
        add_settings_field('cron_enable', __('Cron Enable', CBSE_LANGUAGE_DOMAIN), [$this, 'cronEnable'], 'course_booking_system_extension', $this->sectionHeader);
        add_settings_field('cron_before_time', __('Cron Sent before course', CBSE_LANGUAGE_DOMAIN), [$this,
            'cronBeforeTime'], 'course_booking_system_extension', $this->sectionHeader);
    }

    private function registerSettingsForTags()
    {
        $tags = get_tags(array('taxonomy' => 'mp-event_tag', 'orderby' => 'name', 'hide_empty' => false));
        $values = $this->getOptions('emails');
        foreach ($tags as $tag)
        {
            $value = null;
            if (!empty($values))
            {
                $value = array_filter($values, function ($v, $k) use ($tag)
                {
                    return $v['id'] == $tag->term_id;
                }, ARRAY_FILTER_USE_BOTH);
            }
            $args = array('tag' => $tag, 'value' => $value);
            add_settings_field("email_{$tag->term_id}", $tag->name, [$this, 'tagForPrint'], 'course_booking_system_extension', $this->sectionHeader, $args);
        }
    }

    public function subject()
    {
        $value = esc_attr($this->getOptions('subject') ?? __('Auto print - Sports operation documentation',
                CBSE_LANGUAGE_DOMAIN));
        echo "<input id='subject' name='cbse_auto_print_options[subject]' type='text' value='" . $value . "' />";
        echo "<p class='description'>" . __('Prefix of the subject', CBSE_LANGUAGE_DOMAIN) . "</p>";
    }

    public function validateInput($input): array
    {
        do_action('qm/debug', 'AutoPrintCbseSettings->Validate {input}', ['input' => json_encode($input)]);

        $emails = array();
        $tagsIds = preg_filter('/^email_(.*)/', '$1', array_keys($input));

        foreach ($tagsIds as $tagId)
        {
            $mails = explode(',', $input["email_{$tagId}"]);
            foreach ($mails as $mail)
            {
                if (is_email($mail))
                {
                    $emails[] = array('id' => $tagId, 'mail' => $mail);
                }
            }
        }

        $validatedInput['emails'] = $emails;
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

    public function tagForPrint(array $args)
    {
        $tag = $args['tag'];
        $values = $args['value'] ?? '';
        $value = '';
        if (!empty($values))
        {
            $value = implode(',', array_column($values, 'mail'));
        }
        echo "<input type=\"email\" id=\"email_{$tag->term_id}\" name=\"cbse_auto_print_options[email_{$tag->term_id}]\" value=\"$value\">";
        echo "<p class='description'>" . $tag->description . "</p>";
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
