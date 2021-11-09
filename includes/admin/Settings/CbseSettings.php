<?php

namespace CBSE\Admin\Settings;

abstract class CbseSettings
{
    abstract public function TabName(): string;

    abstract public function TabKey(): string;

    abstract public function RegisterSettings();

    abstract public function RenderSettingsPage();

    /**
     * Checks if this instance is for the key responsible
     * @param $tabKey
     * @return bool
     */
    public function IsTab($tabKey): bool
    {
        return $this->TabKey() === $tabKey;
    }
}
