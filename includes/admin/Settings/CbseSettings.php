<?php

namespace CBSE\Admin\Settings;

/**
 * For the settigns tabs
 */
abstract class CbseSettings
{
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

    /**
     * Add the needed fields
     * @return mixed
     */
    abstract public function registerSettings();

    /**
     * Show the fields for the settings
     * @return mixed
     */
    abstract public function renderSettingsPage();

    /**
     * Checks if this instance is for the key responsible
     * @param $tabKey
     * @return bool
     */
    public function isTab($tabKey): bool
    {
        return $this->tabKey() === $tabKey;
    }
}
