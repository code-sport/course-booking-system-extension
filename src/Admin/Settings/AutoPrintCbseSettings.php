<?php

namespace CBSE\Admin\Settings;

class AutoPrintCbseSettings extends CbseSettings
{

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
        // TODO: Implement registerSettings() method.
    }

    /**
     * @inheritDoc
     */
    public function renderSettingsPage()
    {
        // TODO: Implement renderSettingsPage() method.
    }

    public function validate($input)
    {
        // TODO: Implement Validate() method.
    }
}
