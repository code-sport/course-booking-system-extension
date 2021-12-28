<?php

namespace CBSE;

use CBSE\Admin\User\Model\Covid19Status;
use DateInterval;
use DateTime;
use Exception;

class UserCovid19Status
{
    private ?Covid19Status $status;
    private ?DateTime $date = null;
    private int $userId;


    public function __construct(int $userId)
    {
        $this->status(get_the_author_meta('covid-19-status', $userId));
        $datetime = DateTime::createFromFormat('Y-m-d', get_the_author_meta('covid-19-status_date', $userId));
        if ($datetime)
        {
            $this->date = $datetime;
        }
        $this->userId = $userId;
    }

    private function status(string $userStatus)
    {
        switch ($userStatus)
        {
            default:
            case 'unknown';
                $statusTemp = null;
                break;
            case 'tested';
                $statusTemp = new Covid19Status(
                    'tested',
                    __('tested', CBSE_LANGUAGE_DOMAIN),
                    null,
                    new DateInterval('PT24H'),
                    null
                );
                break;
            case 'vaccinated';
                $statusTemp = new Covid19Status(
                    'vaccinated',
                    __('vaccinated', CBSE_LANGUAGE_DOMAIN),
                    $this->loadCovid19StatusSetting('vaccinated_status_valid_from'),
                    $this->loadCovid19StatusSetting('vaccinated_status_valid_to'),
                    $this->loadCovid19StatusSetting('vaccinated_plus_status_valid_to')
                );
                break;
            case 'vaccinated_updated';
                $statusTemp = new Covid19Status(
                    'vaccinated_updated',
                    __('booster vaccinated', CBSE_LANGUAGE_DOMAIN),
                    $this->loadCovid19StatusSetting('vaccinated_updated_status_valid_from'),
                    $this->loadCovid19StatusSetting('vaccinated_updated_status_valid_to'),
                    $this->loadCovid19StatusSetting('vaccinated_updated_plus_status_valid_to')
                );
                break;
            case 'recovered';
                $statusTemp = new Covid19Status(
                    'recovered',
                    __('recovered', CBSE_LANGUAGE_DOMAIN),
                    $this->loadCovid19StatusSetting('recovered_status_valid_from'),
                    $this->loadCovid19StatusSetting('recovered_status_valid_to'),
                    $this->loadCovid19StatusSetting('recovered_plus_status_valid_to')
                );
                break;
        }

        $this->status = $statusTemp;
    }

    private function loadCovid19StatusSetting(string $setting): ?DateInterval
    {
        $Covid19Options = get_option('cbse_covid19_options');

        if (array_key_exists($setting, $Covid19Options))
        {
            try
            {
                return new DateInterval($Covid19Options[$setting]);
            } catch (Exception $e)
            {
            }
        }

        return null;
    }

    public function getStatusOrAll(): string
    {
        return $this->getValidatedStatus() ??
            static::getAll();
    }

    private function getValidatedStatus($default = null): ?string
    {
        if ($this->isValid())
        {
            if ($this->isPlusStatus())
            {
                return __($this->status->getName(), CBSE_LANGUAGE_DOMAIN) . ' +';
            }
            else
            {
                return __($this->status->getName(), CBSE_LANGUAGE_DOMAIN);
            }
        }
        return $default;
    }

    public function isValid(): bool
    {
        return $this->date != null && $this->status->isValid($this->date);
    }

    private function isPlusStatus(): bool
    {
        return $this->date != null && $this->status->isPlusStatus($this->date);
    }

    public static function getAll($separator = '|'): string
    {
        $all = array(
            __('tested', CBSE_LANGUAGE_DOMAIN),
            __('vaccinated', CBSE_LANGUAGE_DOMAIN),
            __('recovered', CBSE_LANGUAGE_DOMAIN)
        );
        return implode($separator, $all);
    }

    public function getStatus(): string
    {
        return $this->status->getName();
    }

    public function getStatusOrEmpty(): string
    {
        return $this->getValidatedStatus('');
    }

    /**
     * @return DateTime
     */
    public function getDateFormatted(): string
    {
        if ($this->date)
        {
            return $this->date->format(get_option('date_format'));
        }
        return '';
    }

    public function getFlags(): ?string
    {
        $flags = array();

        if (get_the_author_meta('covid-19-status_employee', $this->userId) == "1")
        {
            $flags[] = __("E", CBSE_LANGUAGE_DOMAIN);
        }
        if (get_the_author_meta('covid-19-status_top-athlete', $this->userId) == "1")
        {
            $flags[] = __("TA", CBSE_LANGUAGE_DOMAIN);
        }

        if (empty($flags))
        {
            return null;
        }

        return implode(',', $flags);
    }
}
