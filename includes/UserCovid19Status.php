<?php

namespace CBSE;

use DateTime;

class UserCovid19Status
{
    private string $status;
    private string $date;

    public function __construct(int $userId)
    {
        $this->status = get_the_author_meta('covid-19-status', $userId);
        $this->date = get_the_author_meta('covid-19-status_date', $userId);
    }

    public function getStatusOrAll(): string
    {
        return __($this->getValidatedStatus(), 'course-booking-system-extension') ??
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
        $today = new DateTime("today");
        $diff = $today->diff($date);
        // Extract days count in interval
        $diffDays = (integer)$diff->format("%R%a");
        return $diffDays == 0;
    }

    private function validateVaccinated(DateTime $date): bool
    {
        $today = new DateTime("today");
        $diff = $today->diff($date);
        // Extract days count in interval
        $diffDays = (integer)$diff->format("%R%a");
        return ($diffDays < -14) && ($diffDays > -365);
    }

    private function validateRecovered(DateTime $date): bool
    {
        $today = new DateTime("today");
        $diff = $today->diff($date);
        // Extract days count in interval
        $diffDays = (integer)$diff->format("%R%a");
        return ($diffDays < -28) && ($diffDays > -182);
    }

    public static function getAll($separator = '|'): string
    {
        $all = array(
            __('tested', 'course-booking-system-extension'),
            __('vaccinated', 'course-booking-system-extension'),
            __('recovered', 'course-booking-system-extension')
        );
        return implode($separator, $all);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStatusOrEmpty(): string
    {
        return __($this->getValidatedStatus(), 'course-booking-system-extension');
    }
}
