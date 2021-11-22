<?php

namespace CBSE\Admin\Settings;

/**
 * For the settings tabs
 */
abstract class CbseSettings
{
    protected $optionGroup;
    protected $optionName;

    public function __construct($optionGroup, $optionName)
    {
        $this->optionGroup = $optionGroup;
        $this->optionName = $optionName;

        register_setting($this->optionGroup, $this->optionName, [$this, 'validate']);

        //section name, form element name, callback for sanitization
        add_option($this->optionName, array());
    }

    /**
     * @return mixed
     */
    public function getOptionGroup()
    {
        return $this->optionGroup;
    }

    public function registerSettings()
    {
        settings_fields($this->optionGroup);
    }

    abstract public function validate($input);

    /**
     * Show the fields for the settings
     *
     * @return mixed
     */
    abstract public function renderSettingsPage();

    /**
     * @param $tabKey
     *
     * @return string
     */
    public function getTabHtmlLink($tabKey): string
    {
        return '<a class="nav-tab  ' . $this->isTabActive($tabKey) . '"'
            . 'href="' . $this->getAdminUrl() . '">' . $this->tabName() . '</a>';


    }

    public function isTabActive($tabKey): string
    {
        return $this->isTab($tabKey) ? 'nav-tab-active' : '';
    }

    /**
     * Checks if this instance is for the key responsible
     *
     * @param $tabKey
     *
     * @return bool
     */
    public function isTab($tabKey): bool
    {
        return $this->tabKey() === $tabKey;
    }

    /**
     * The key of the tab
     *
     * @return string
     */
    abstract public function tabKey(): string;

    private function getAdminUrl()
    {
        return admin_url('options-general.php?page=course_booking_system_extension&tab=' . $this->tabKey());
    }

    /**
     * The shown name on the tab
     *
     * @return string
     */
    abstract public function tabName(): string;

    protected function getOptions($setting)
    {
        $option = get_option($this->optionName);
        if ($option != null && array_key_exists($setting, $option))
        {
            return $option[$setting];
        }

        return null;
    }
}
