<?php

namespace CBSE\Admin\Settings;

class MailCoachCbseSettings extends CbseSettings
{
    public function __construct()
    {
        parent::__construct('cbse_mail', 'cbse_mail_options');
    }

    /**
     * @inheritDoc
     */
    public function tabName(): string
    {
        return  __('Mail - Coach', 'course_booking_system_extension');
    }

    /**
     * @inheritDoc
     */
    public function tabKey(): string
    {
        return 'mailcoach';
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
