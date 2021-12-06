<?php

namespace CBSE;

use CBSE\Model\Covid19Status;
use DateInterval;
use DateTime;

class UserCovid19Status
{
    private ?Covid19Status $status;
    private bool $plusStatePossible = false;
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
                    new DateInterval('PT24H')
                );
                break;
            case 'vaccinated';
                $statusTemp = new Covid19Status(
                    'vaccinated',
                    __('vaccinated', CBSE_LANGUAGE_DOMAIN),
                    new DateInterval('P14D'),
                    new DateInterval('P9M')
                );
                $this->plusStatePossible = true;
                break;
            case 'vaccinated_updated';
                $statusTemp = new Covid19Status(
                    'vaccinated_updated',
                    __('booster vaccinated', CBSE_LANGUAGE_DOMAIN),
                    null,
                    new DateInterval('P9M')
                );
                break;
            case 'recovered';
                $statusTemp = new Covid19Status(
                    'recovered',
                    __('recovered', CBSE_LANGUAGE_DOMAIN),
                    new DateInterval('P28D'),
                    new DateInterval('P6M')
                );
                $this->plusStatePossible = true;
                break;
        }

        $this->status = $statusTemp;
    }

    public function getStatusOrAll(): string
    {
        return $this->getValidatedStatus() ??
            UserCovid19Status::getAll();
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

    /**
     * @return bool
     */
    private function isPlusStatus(): bool
    {
        return $this->plusStatePossible
            && ($this->date != null)
            && Covid19Status::isInUpToInterval($this->date, new DateInterval('P6M'));
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
            $flags[] = "E";
        }
        if (get_the_author_meta('covid-19-status_top-athlete', $this->userId) == "1")
        {
            $flags[] = "TA";
        }

        if (empty($flags))
        {
            return null;
        }

        return implode(',', $flags);
    }
}
