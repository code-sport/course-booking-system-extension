<?php

namespace CBSE\Cron;

use DateInterval;
use DateTime;
use Exception;

class DocumentationPrint extends CronBase
{
    private static ?DocumentationPrint $instance = null;

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct()
    {
        parent::__construct('cbse_cron_documentation_print_hook');
    }

    /**
     * gets the instance via lazy initialization (created on first usage)
     */
    public static function getInstance(): DocumentationPrint
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    protected function work(DateTime $dateLastRun, DateTime $dateNow)
    {
        $interval = new DateInterval('PT15M');

        $dateFrom = clone $dateLastRun;
        $dateFrom->add($interval);
        $dateTo = clone $dateNow;
        $dateTo->add($interval);

        $courses = cbse_courses_in_time($dateFrom, $dateTo);

        foreach ($courses as $course)
        {
            $userId = ($course->substitutes_user_id ?? $course->user_id);
            $autoPrint = empty(get_the_author_meta('cbse-auto-print', $userId)) ? 0 : get_the_author_meta('cbse-auto-inform', $userId);

            if ($autoPrint)
            {
                //TODO cbse_sent_mail_with_course_date_bookings($course->course_id, $course->date, $userId);
            }

        }
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }
}