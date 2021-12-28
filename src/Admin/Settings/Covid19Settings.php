<?php

namespace CBSE\Admin\Settings;

class Covid19Settings extends CbseSettings
{

    private string $sectionHeader = 'cbse_covid19';

    public function __construct()
    {
        parent::__construct('cbse_covid19', 'cbse_covid19_options');
    }

    /**
     * @inheritDoc
     */
    public function tabName(): string
    {
        return __('Covid 19', CBSE_LANGUAGE_DOMAIN);
    }

    /**
     * @inheritDoc
     */
    public function tabKey(): string
    {
        return 'covid19';
    }

    /**
     * @inheritDoc
     */
    public function registerSettings()
    {
        //section name, display name, callback to print description of section, page to which section is attached.
        add_settings_section($this->sectionHeader, __('Covid 19', CBSE_LANGUAGE_DOMAIN), [$this, 'covid19General'], 'course_booking_system_extension');

        //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
        //last field section is optional.
        /* vaccinated */
        add_settings_field('vaccinated_status_valid_from', __('vaccinated status valid from', CBSE_LANGUAGE_DOMAIN), [$this, 'vaccinatedStatusValidFrom'], 'course_booking_system_extension', $this->sectionHeader);
        add_settings_field('vaccinated_status_valid_to', __('vaccinated status valid to', CBSE_LANGUAGE_DOMAIN), [$this, 'vaccinatedStatusValidTo'], 'course_booking_system_extension', $this->sectionHeader);
        add_settings_field('vaccinated_plus_status_valid_to', __('vaccinated plus status valid to', CBSE_LANGUAGE_DOMAIN), [$this, 'vaccinatedPlusStatusValidTo'], 'course_booking_system_extension', $this->sectionHeader);

        /* vaccinated_updated*/
        add_settings_field('vaccinated_updated_status_valid_from', __('vaccinated updated status valid from', CBSE_LANGUAGE_DOMAIN), [$this, 'vaccinatedUpdatedStatusValidFrom'], 'course_booking_system_extension', $this->sectionHeader);
        add_settings_field('vaccinated_updated_status_valid_to', __('vaccinated updated status valid to', CBSE_LANGUAGE_DOMAIN), [$this, 'vaccinatedUpdatedStatusValidTo'], 'course_booking_system_extension', $this->sectionHeader);
        add_settings_field('vaccinated_updated_plus_status_valid_to', __('vaccinated updated plus status valid to', CBSE_LANGUAGE_DOMAIN), [$this, 'vaccinatedUpdatedPlusStatusValidTo'], 'course_booking_system_extension', $this->sectionHeader);

        /* recovered */
        add_settings_field('recovered_status_valid_from', __('recovered status valid from', CBSE_LANGUAGE_DOMAIN), [$this, 'recoveredStatusValidFrom'], 'course_booking_system_extension', $this->sectionHeader);
        add_settings_field('recovered_status_valid_to', __('recovered status valid to', CBSE_LANGUAGE_DOMAIN), [$this, 'recoveredStatusValidTo'], 'course_booking_system_extension', $this->sectionHeader);
        add_settings_field('recovered_plus_status_valid_to', __('recovered plus status valid to', CBSE_LANGUAGE_DOMAIN), [$this, 'recoveredPlusStatusValidTo'], 'course_booking_system_extension', $this->sectionHeader);
    }

    public function covid19General()
    {
        $text = '<p>';
        $text .= __('Settings for the Covid-19 status validities.', CBSE_LANGUAGE_DOMAIN);
        $text .= '</p>';

        echo $text;
    }

    /* vaccinated */
    public function vaccinatedStatusValidFrom()
    {
        $this->showSettingField('vaccinated_status_valid_from', 'P14D');
    }

    private function showSettingField(string $setting, string $default)
    {
        $value = esc_attr($this->getOptions($setting) ?? $default);
        echo "<input id='{$setting}' name='cbse_covid19_options[{$setting}]' type='text' value='" . $value . "'   pattern='((P([1-9]|[1-9][0-9])(W|D|M|H)|PT([1-9]|[1-9][0-9])H)|^$)' />";
        echo "<p class='description'>" . __('Based on DateInterval::__construct', CBSE_LANGUAGE_DOMAIN) . "</p>";
    }

    public function vaccinatedStatusValidTo()
    {
        $this->showSettingField('vaccinated_status_valid_to', 'P9M');
    }

    /* vaccinated_updated*/

    public function vaccinatedPlusStatusValidTo()
    {
        $this->showSettingField('vaccinated_plus_status_valid_to', 'P3M');
    }

    public function vaccinatedUpdatedStatusValidFrom()
    {
        $this->showSettingField('vaccinated_updated_status_valid_from', '');
    }

    public function vaccinatedUpdatedStatusValidTo()
    {
        $this->showSettingField('vaccinated_updated_status_valid_to', 'P9M');
    }

    /* recovered */

    public function vaccinatedUpdatedPlusStatusValidTo()
    {
        $this->showSettingField('vaccinated_updated_plus_status_valid_to', '');
    }

    public function recoveredStatusValidFrom()
    {
        $this->showSettingField('recovered_status_valid_from', 'P28D');
    }

    public function recoveredStatusValidTo()
    {
        $this->showSettingField('recovered_status_valid_to', 'P6M');
    }

    public function recoveredPlusStatusValidTo()
    {
        $this->showSettingField('recovered_plus_status_valid_to', 'P3M');
    }

    /**
     * @inheritDoc
     */
    public function renderSettingsPage()
    {
        settings_fields($this->sectionHeader);
    }

    public function validateInput($input)
    {
        do_action('qm/debug', 'Covid19Settings->Validate {input}', ['input' => json_encode($input),]);
        return $input;
    }
}