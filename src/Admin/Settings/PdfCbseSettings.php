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
        add_settings_section($this->sectionHeader, __('Header of PDF', CBSE_LANGUAGE_DOMAIN), [$this, 'sectionPdfHeaderText'], 'course_booking_system_extension');


        //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
        //last field section is optional.
        add_settings_field('header_image_attachment_id',
            __('Image Attachment ID', CBSE_LANGUAGE_DOMAIN),
            [$this, 'headerImage'],
            'course_booking_system_extension',
            $this->sectionHeader);

        add_settings_field('title',
            __('Title', CBSE_LANGUAGE_DOMAIN),
            [$this, 'title'],
            'course_booking_system_extension',
            $this->sectionHeader);
    }

    public function sectionPdfHeaderText()
    {
        echo '<p>' . __('Here you can set all header options for generated pdf.', CBSE_LANGUAGE_DOMAIN)
            . '</p>';
    }

    public function headerImage()
    {
        $value = esc_attr($this->getOptions('header_image_attachment_id') ?? "");
        echo "<input id='header_image_attachment_id' name='cbse_pdf_header_options[header_image_attachment_id]' type='text' value='" . $value . "' />";
    }

    public function title()
    {
        $value = esc_attr($this->getOptions('title') ?? __('Sports operation documentation', CBSE_LANGUAGE_DOMAIN));
        echo "<input id='subject' name='cbse_pdf_header_options[title]' type='text' value='" . $value . "' />";
        echo "<p class='description'>" . __('Title for the pdf.', CBSE_LANGUAGE_DOMAIN) . "</p>";
    }


    public function validateInput($input)
    {
        do_action('qm/debug', 'PdfCbseSettings->Validate {input}', ['input' => json_encode($input),]);

        /*if ($input !== null)
        {
            $validatedInput['header_image_attachment_id'] = trim($input['header_image_attachment_id']);
            $validatedInput['categories_exclude'] = trim($input['categories_exclude']);
            return $validatedInput;
        }
        return array();*/
        return $input;
    }


    public function renderSettingsPage()
    {
        settings_fields($this->sectionHeader);
    }


}
