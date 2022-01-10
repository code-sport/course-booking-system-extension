<?php

namespace CBSE\Admin\Settings;

class LicensesSettings extends CbseSettings
{

    public function __construct()
    {
        parent::__construct('cbse_licenses', 'cbse_licenses_options');
    }

    public function validateInput($input)
    {

    }

    /**
     * @inheritDoc
     */
    public function renderSettingsPage()
    {
        // settings_fields($this->sectionHeader);

        $this->sectionLicenses();
    }

    public function sectionLicenses()
    {
        $text = '<p>';
        $text .= __('Here you can see all used dependencies.', CBSE_LANGUAGE_DOMAIN);
        $text .= '</p>';

        $text .= '<ul>';
        foreach ($this->getLicences() as $license)
        {
            $text .= "<li><a href='{$license['Url']}'>{$license['Name']} ({$license['License']})</a> </li>";
        }
        $text .= '</ul>';

        echo $text;
    }

    private function getLicences(): array
    {
        return array(array('Name' => 'tecnickcom/tcpdf', 'License' => 'LGPL-3.0-only', 'Url' => 'https://tcpdf.org/'), array('Name' => 'Analog', 'License' => 'MIT License', 'Url' => 'https://github.com/jbroadway/analog'), array('Name' => 'icalendar-generator', 'License' => 'MIT License', 'Url' => 'https://github.com/spatie/icalendar-generator'));
    }

    /**
     * @inheritDoc
     */
    public function tabKey(): string
    {
        return 'licenses';
    }

    /**
     * @inheritDoc
     */
    public function tabName(): string
    {
        return __('Licenses', CBSE_LANGUAGE_DOMAIN);
    }
}
