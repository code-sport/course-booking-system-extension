<?php

namespace CBSE\Admin\Settings;

class PdfCbseSettings extends CbseSettings
{
    public function TabName(): string
    {
        return __('PDF', 'course_booking_system_extension');
    }

    public function TabKey(): string
    {
       return 'pdf';
    }

    public function RegisterSettings()
    {
        add_settings_section('cbse_pdf_header', __('Header of PDF', 'course_booking_system_extension'), [$this, 'SectionPdfHeaderText'], 'course_booking_system_extension');
    }

    public function SectionPdfHeaderText()
    {
        echo '<p>' . _e('Here you can set all header options for generated pdf.', 'course-booking-system-extension') . '</p>';
    }

    public function RenderSettingsPage()
    {
        settings_fields('cbse_pdf_header');
    }

}
