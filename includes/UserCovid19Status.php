<?php

namespace CBSE;

class UserCovid19Status
{
    private int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function getStatusOrAll(): string
    {
        return __($this->getStatus(), 'course-booking-system-extension') ??
            UserCovid19Status::getAll();
    }

    /**
     * @return string
     */
    private function getStatus(): string
    {
        //TODO: Validate
        return get_the_author_meta('covid-19-status', $this->userId);
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

    public function getStatusOrEmpty(): string
    {
        return __($this->getStatus(), 'course-booking-system-extension');
    }
}