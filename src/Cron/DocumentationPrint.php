<?php

namespace CBSE\Cron;

use CBSE\DocumentationMail;
use CBSE\Dto\CourseInfoDate;
use CBSE\Dto\CoursesInTime;
use CBSE\Helper\ArrayHelper;
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

        $coursesInTime = new CoursesInTime($dateFrom, $dateTo);
        $courses = $coursesInTime->getCourses();

        foreach ($courses as $course)
        {
            $userId = ($course->substitutes_user_id ?? $course->user_id);
            $autoPrintUser = empty(get_the_author_meta('cbse-auto-print', $userId)) ? 0 : get_the_author_meta('cbse-auto-inform', $userId);


            if ($autoPrintUser)
            {
                $date = DateTime::createFromFormat('Y-m-d', $course->date);
                $courseInfo = new CourseInfoDate($course->course_id, $date);
                $printerMails = $this->getPrinterMailAddresses($courseInfo);

                if (!empty($printerMails))
                {
                    $documentationMail = new DocumentationMail($courseInfo);
                    $documentationMail->sentToUser(array_column($printerMails, 'mail'));
                }

            }

        }
    }

    private function getPrinterMailAddresses(CourseInfoDate $course): array
    {
        $printOptionsEmails = get_option('cbse_auto_print_options')['emails'];
        $eventTagIds = ArrayHelper::Column($course->getEventTags(), 'term_id');

        return array_filter($printOptionsEmails, function ($v, $k) use ($eventTagIds)
        {
            return in_array($v['id'], $eventTagIds);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }
}