<?php

namespace CBSE\Admin\Settings;

class GeneralCbseSettings extends CbseSettings
{
    public function __construct()
    {
        parent::__construct('cbse_general', 'cbse_general_options');
    }

    /**
     * @inheritDoc
     */
    public function tabName(): string
    {
        return __('General', 'course_booking_system_extension');
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
        // TODO: Implement registerSettings() method.
    }

    /**
     * @inheritDoc
     */
    public function renderSettingsPage()
    {
        // TODO: Implement renderSettingsPage() method.
    }

    public function Validate($input)
    {
        // TODO: Implement Validate() method.
    }
}
