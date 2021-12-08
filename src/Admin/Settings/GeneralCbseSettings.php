<?php

namespace CBSE\Admin\Settings;

class GeneralCbseSettings extends CbseSettings
{
    private string $sectionHeader = 'cbse_general';

    public function __construct()
    {
        parent::__construct('cbse_general', 'cbse_general_options');
    }

    /**
     * @inheritDoc
     */
    public function tabName(): string
    {
        return __('General',CBSE_LANGUAGE_DOMAIN);
    }

    /**
     * @inheritDoc
     */
    public function tabKey(): string
    {
        return 'general';
    }

    /**
     * @inheritDoc
     */
    public function registerSettings()
    {
        //section name, display name, callback to print description of section, page to which section is attached.
        add_settings_section($this->sectionHeader,
            __('General', 'course_booking_system_extension'),
            [$this, 'sectionGeneral'],
            'course_booking_system_extension');

        //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
        //last field section is optional.
        add_settings_field('mail_categories_title',
            __('Title for Categories', 'course_booking_system_extension'),
            [$this, 'categoriesTitle'],
            'course_booking_system_extension',
            $this->sectionHeader);
        add_settings_field('mail_categories_exclude',
            __('Exclude Categories', 'course_booking_system_extension'),
            [$this, 'categoriesExclude'],
            'course_booking_system_extension',
            $this->sectionHeader);
        add_settings_field('mail_tags_title',
            __('Title for Tags', 'course_booking_system_extension'),
            [$this, 'tagsTitle'],
            'course_booking_system_extension',
            $this->sectionHeader);
        add_settings_field('mail_tags_exclude',
            __('Exclude Tags',
                'course_booking_system_extension'),
            [$this, 'tagsExclude'],
            'course_booking_system_extension',
            $this->sectionHeader);

    }

    public function sectionGeneral()
    {
        $text = '<p>';
        $text .= __('Here you can set all general options.', CBSE_LANGUAGE_DOMAIN);
        $text .= '</p>';

        echo $text;
    }

    /* Categories */
    public function categoriesTitle()
    {
        $value = esc_attr($this->getOptions('categories_title') ?? __('Categories', CBSE_LANGUAGE_DOMAIN));
        echo "<input id='mail_categories_title' name='cbse_general_options[categories_title]' type='text' value='" . $value . "' />";
    }

    public function categoriesExclude()
    {
        $value = esc_attr($this->getOptions('categories_exclude') ?? "");
        echo "<input id='mail_categories_exclude' name='cbse_general_options[categories_exclude]' type='text' value='" . $value . "' />";
        echo "<p class='description'>" . __('0 will hide this field. Please add the values comma seperated.', CBSE_LANGUAGE_DOMAIN) . "</p>";
    }

    /* Tags */
    public function tagsTitle()
    {
        $value = esc_attr($this->getOptions('tags_title') ?? __('Tags', CBSE_LANGUAGE_DOMAIN));
        echo "<input id='mail_tags_title' name='cbse_general_options[tags_title]' type='text' value='" . $value . "' />";
    }

    public function tagsExclude()
    {
        $value = esc_attr($this->getOptions('tags_exclude') ?? "");
        echo "<input id='mail_tags_exclude' name='cbse_general_options[tags_exclude]' type='text' value='" . $value . "' />";
        echo "<p class='description'>" . __('0 will hide this field. Please add the values comma seperated.', CBSE_LANGUAGE_DOMAIN) . "</p>";
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
        do_action('qm/debug', 'GeneralCbseSettings->Validate {input}', ['input' => json_encode($input),]);
        return $input;
    }
}
