<?php

namespace CBSE\Admin\Settings;

class PdfCbseSettings extends CbseSettings
{
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
        add_settings_section(
            'cbse_pdf_header',
            __('Header of PDF', 'course_booking_system_extension'),
            [$this, 'sectionPdfHeaderText'],
            'course_booking_system_extension'
        );
    }

    public function sectionPdfHeaderText()
    {
        echo '<p>'
            . _e('Here you can set all header options for generated pdf.', 'course-booking-system-extension')
            . '</p>';
    }


    public function Validate($input)
    {
       return $input;
    }


    public function renderSettingsPage()
    {
        // TODO: Implement renderSettingsPage() method.
    }
}
