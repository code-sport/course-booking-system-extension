<?php

namespace CBSE\Admin\Settings;

class PdfCbseSettings extends CbseSettings
{
    private string $sectionHeader = 'cbse_pdf_header';

    public function __construct()
    {
        parent::__construct('cbse_pdf_header', 'cbse_pdf_header_options');
    }

    public function tabName(): string
    {
        return __('PDF', 'course_booking_system_extension');
    }

    public function tabKey(): string
    {
        return 'pdf';
    }

    public function registerSettings()
    {
        //section name, display name, callback to print description of section, page to which section is attached.
        add_settings_section($this->sectionHeader, __('Header of PDF', 'course_booking_system_extension'), [$this, 'sectionPdfHeaderText'], 'course_booking_system_extension');


        //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
        //last field section is optional.
        add_settings_field('categories_exclude', __('Exclude Categories', 'course_booking_system_extension'), [$this, 'categoriesExclude'], 'course_booking_system_extension', $this->sectionHeader);
    }

    public function sectionPdfHeaderText()
    {
        echo '<p>' . _e('Here you can set all header options for generated pdf.', 'course-booking-system-extension') . '</p>';
    }


    public function Validate($input): array
    {
        do_action('qm/debug', 'PdfCbseSettings->Validate {input}', ['input' => json_encode($input),]);

        if ($input !== null)
        {
            $validatedInput['categories_exclude'] = trim($input['categories_exclude']);
            return $validatedInput;
        }
        return array();
    }


    public function renderSettingsPage()
    {
        settings_fields($this->sectionHeader);
    }

    function categoriesExclude()
    {
        $value = esc_attr($this->getOptions('categories_exclude') ?? "");
        echo "<input id='categories_exclude' name='cbse_pdf_header_options[categories_exclude]' type='text' value='" . $value . "' />";
        echo "<p class='description'>" . __('0 will hide this field. Please add the values comma seperated.', 'course-booking-system-extension') . "</p>";
    }
}
