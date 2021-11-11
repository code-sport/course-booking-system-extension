<?php

namespace CBSE\Admin\Settings;

/**
 * For the settigns tabs
 */
abstract class CbseSettings
{
    protected $optionGroup;
    protected $optionName;

    public function __construct($option_group, $option_name)
    {
        $this->optionGroup = $option_group;
        $this->optionName = $option_name;

        register_setting($this->optionGroup, $this->optionName, [$this, 'Validate']);

        //section name, form element name, callback for sanitization
        add_option($this->optionName, null);
    }

    /**
     * @return mixed
     */
    public function getOptionGroup()
    {
        return $this->optionGroup;
    }

    /**
     * The shown name on the tab
     * @return string
     */
    abstract public function tabName(): string;

    /**
     * The key of the tab
     * @return string
     */
    abstract public function tabKey(): string;

    public function registerSettings()
    {
        settings_fields($this->optionGroup);
    }

    abstract public function Validate($input);

    /**
     * Show the fields for the settings
     * @return mixed
     */
    abstract public function renderSettingsPage();

    /**
     * @param $tabKey
     * @return string
     */
    public function getTabHtmlLink($tabKey): string
    {
        return '<a class="nav-tab  ' . $this->isTabActive($tabKey) . '"'
            . 'href="' . $this->getAdminUrl() . '">'
            . $this->tabName()
            . '</a>';


    }

    /**
     * Checks if this instance is for the key responsible
     * @param $tabKey
     * @return bool
     */
    public function isTab($tabKey): bool
    {
        return $this->tabKey() === $tabKey;
    }

    public function isTabActive($tabKey): string
    {
        return $this->isTab($tabKey) ? 'nav-tab-active' : '';
    }

    private function getAdminUrl()
    {
        return admin_url('options-general.php?page=course_booking_system_extension&tab=' . $this->tabKey());
    }
}
