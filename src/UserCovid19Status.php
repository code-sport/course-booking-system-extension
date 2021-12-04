<?php

namespace CBSE;

use CBSE\Dto\Covid19StatusItems;
use DateTime;

class UserCovid19Status
{
    private string $status;
    private string $date;
    private Covid19StatusItems $covid19StatusItems;

    public function __construct(int $userId)
    {
        $this->status = get_the_author_meta('covid-19-status', $userId);
        $this->date = get_the_author_meta('covid-19-status_date', $userId);
        $this->covid19StatusItems = Covid19StatusItems::getInstance();
    }

    public function getStatusOrAll(): string
    {
        return __($this->getValidatedStatus(), CBSE_LANGUAGE_DOMAIN) ??
            UserCovid19Status::getAll();
    }

    private function getValidatedStatus(): string
    {
        if ($this->validate())
        {
            return $this->status;
        }
        return '';
    }

    public function validate(): bool
    {
        $valid = false;
        $dateTime = DateTime::createFromFormat('Y-m-d', $this->date);
        switch ($this->status)
        {
            default:
            case 'unknown';
                break;
            case 'tested';
                $valid = $this->validateTest($dateTime);
                break;
            case 'vaccinated';
                $valid = $this->validateVaccinated($dateTime);
                break;

            case 'recovered';
                $valid = $this->validateRecovered($dateTime);
                break;
        }
        return $valid;
    }

    private function validateTest(DateTime $date): bool
    {
        $tested = $this->covid19StatusItems->getTested();
        return $tested->isValid($date);
    }

    private function validateVaccinated(DateTime $date): bool
    {
        $vaccinated = $this->covid19StatusItems->getVaccinated();
        return $vaccinated->isValid($date);
    }

    private function validateRecovered(DateTime $date): bool
    {
        $recovered = $this->covid19StatusItems->getRecovered();
        return $recovered->isValid($date);
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
        return $this->status;
    }

    public function getStatusOrEmpty(): string
    {
        return __($this->getValidatedStatus(), CBSE_LANGUAGE_DOMAIN);
    }
}
