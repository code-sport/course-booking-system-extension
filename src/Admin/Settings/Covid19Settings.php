<?php

namespace CBSE\Admin\User\Admin\User\User\Settings;

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
        $value = esc_attr($this->getOptions('vaccinated_status_valid_from') ?? 'P14D');
        echo "<input id='vaccinated_status_valid_from' name='cbse_covid19_options[vaccinated_status_valid_from]' type='text' value='" . $value . "'   pattern='((P([1-9]|[1-9][0-9])(W|D|M|H)|PT([1-9]|[1-9][0-9])H)|^$)' />";
        echo "<p class='description'>" . __('Based on DateInterval::__construct', CBSE_LANGUAGE_DOMAIN) . "</p>";
    }

    public function vaccinatedStatusValidTo()
    {
        $value = esc_attr($this->getOptions('vaccinated_status_valid_to') ?? 'P9M');
        echo "<input id='vaccinated_status_valid_to' name='cbse_covid19_options[vaccinated_status_valid_to]' type='text' value='" . $value . "'   pattern='((P([1-9]|[1-9][0-9])(W|D|M|H)|PT([1-9]|[1-9][0-9])H)|^$)' />";
        echo "<p class='description'>" . __('Based on DateInterval::__construct', CBSE_LANGUAGE_DOMAIN) . "</p>";
    }

    public function vaccinatedPlusStatusValidTo()
    {
        $value = esc_attr($this->getOptions('vaccinated_plus_status_valid_to') ?? 'P3M');
        echo "<input id='vaccinated_plus_status_valid_to' name='cbse_covid19_options[vaccinated_plus_status_valid_to]' type='text' value='" . $value . "'   pattern='((P([1-9]|[1-9][0-9])(W|D|M|H)|PT([1-9]|[1-9][0-9])H)|^$)' />";
        echo "<p class='description'>" . __('Based on DateInterval::__construct', CBSE_LANGUAGE_DOMAIN) . "</p>";
    }

    /* vaccinated_updated*/
    public function vaccinatedUpdatedStatusValidFrom()
    {
        $value = esc_attr($this->getOptions('vaccinated_updated_status_valid_from') ?? 'P0D');
        echo "<input id='vaccinated_updated_status_valid_from' name='cbse_covid19_options[vaccinated_updated_status_valid_from]' type='text' value='" . $value . "'   pattern='((P([1-9]|[1-9][0-9])(W|D|M|H)|PT([1-9]|[1-9][0-9])H)|^$)' />";
        echo "<p class='description'>" . __('Based on DateInterval::__construct', CBSE_LANGUAGE_DOMAIN) . "</p>";
    }

    public function vaccinatedUpdatedStatusValidTo()
    {
        $value = esc_attr($this->getOptions('vaccinated_updated_status_valid_to') ?? 'P9M');
        echo "<input id='vaccinated_updated_status_valid_to' name='cbse_covid19_options[vaccinated_updated_status_valid_to]' type='text' value='" . $value . "'   pattern='((P([1-9]|[1-9][0-9])(W|D|M|H)|PT([1-9]|[1-9][0-9])H)|^$)' />";
        echo "<p class='description'>" . __('Based on DateInterval::__construct', CBSE_LANGUAGE_DOMAIN) . "</p>";
    }

    public function vaccinatedUpdatedPlusStatusValidTo()
    {
        $value = esc_attr($this->getOptions('vaccinated_updated_plus_status_valid_to') ?? '');
        echo "<input id='vaccinated_updated_plus_status_valid_to' name='cbse_covid19_options[vaccinated_updated_plus_status_valid_to]' type='text' value='" . $value . "'   pattern='((P([1-9]|[1-9][0-9])(W|D|M|H)|PT([1-9]|[1-9][0-9])H)|^$)' />";
        echo "<p class='description'>" . __('Based on DateInterval::__construct', CBSE_LANGUAGE_DOMAIN) . "</p>";
    }

    /* recovered */
    public function recoveredStatusValidFrom()
    {
        $value = esc_attr($this->getOptions('recovered_status_valid_from') ?? 'P28D');
        echo "<input id='recovered_status_valid_from' name='cbse_covid19_options[recovered_status_valid_from]' type='text' value='" . $value . "'   pattern='((P([1-9]|[1-9][0-9])(W|D|M|H)|PT([1-9]|[1-9][0-9])H)|^$)' />";
        echo "<p class='description'>" . __('Based on DateInterval::__construct', CBSE_LANGUAGE_DOMAIN) . "</p>";
    }

    public function recoveredStatusValidTo()
    {
        $value = esc_attr($this->getOptions('recovered_status_valid_to') ?? 'P6M');
        echo "<input id='recovered_status_valid_to' name='cbse_covid19_options[recovered_status_valid_to]' type='text' value='" . $value . "'   pattern='((P([1-9]|[1-9][0-9])(W|D|M|H)|PT([1-9]|[1-9][0-9])H)|^$)' />";
        echo "<p class='description'>" . __('Based on DateInterval::__construct', CBSE_LANGUAGE_DOMAIN) . "</p>";
    }

    public function recoveredPlusStatusValidTo()
    {
        $value = esc_attr($this->getOptions('recovered_plus_status_valid_to') ?? 'P3M');
        echo "<input id='recovered_plus_status_valid_to' name='cbse_covid19_options[recovered_plus_status_valid_to]' type='text' value='" . $value . "'   pattern='((P([1-9]|[1-9][0-9])(W|D|M|H)|PT([1-9]|[1-9][0-9])H)|^$)' />";
        echo "<p class='description'>" . __('Based on DateInterval::__construct', CBSE_LANGUAGE_DOMAIN) . "</p>";
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