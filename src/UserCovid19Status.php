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


    public function __construct(int $userId)
    {
        $this->status(get_the_author_meta('covid-19-status', $userId));
        $datetime = DateTime::createFromFormat('Y-m-d', get_the_author_meta('covid-19-status_date', $userId));
        if ($datetime)
        {
            $this->date = $datetime;
        }
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
        if ($this->validate())
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

    private function validate(): bool
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
}
