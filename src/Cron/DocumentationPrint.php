<?php

namespace CBSE\Cron;

use Analog\Analog;
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
        $options = get_option('cbse_auto_print_options');
        $hour = is_numeric($options['cron_before_time_hour']) && (int)$options['cron_before_time_hour'] > 0 && (int)$options['cron_before_time_hour'] < 24 ? $options['cron_before_time_hour'] : 0;
        $minute = is_numeric($options['cron_before_time_minute']) && (int)$options['cron_before_time_minute'] > 0 && (int)$options['cron_before_time_minute'] < 60 ? $options['cron_before_time_minute'] : 20;

        try
        {
            $interval = new DateInterval('PT' . $hour . 'H' . $minute . 'M');
        } catch (Exception $e)
        {
            $interval = new DateInterval('PT20M');
        }

        $dateFrom = clone $dateLastRun;
        $dateFrom->add($interval);
        $dateTo = clone $dateNow;
        $dateTo->add($interval);

        Analog::log(get_class($this) . ' - ' . __FUNCTION__ . ' - Runs at ' . $dateNow->format('c') . ' for interval ' . $interval->format('%H:%I'));

        $coursesInTime = new CoursesInTime($dateFrom, $dateTo);
        $courses = $coursesInTime->getCourses();

        foreach ($courses as $course)
        {
            $this->workOnCourse($course);
        }
    }

    /**
     * @param $course
     *
     * @return void
     */
    protected function workOnCourse($course): void
    {
        try
        {
            $userId = ($course->substitutes_user_id ?? $course->user_id);
            if (get_userdata($userId) !== false)
            {
                $autoPrintUser = empty(get_the_author_meta('cbse-auto-print', $userId)) ? 0 : get_the_author_meta('cbse-auto-print', $userId);

                Analog::log(get_class($this) . ' - ' . __FUNCTION__ . ' - Course: ' . $course->course_id . ' print: ' . $autoPrintUser);

                if ($autoPrintUser)
                {
                    $date = DateTime::createFromFormat('Y-m-d', $course->date);
                    $courseInfo = new CourseInfoDate($course->course_id, $date);
                    $printerMails = $this->getPrinterMailAddresses($courseInfo);
                    Analog::log(get_class($this) . ' - ' . __FUNCTION__ . ' - print on: ' . implode(', ', $printerMails));

                    if (!empty($printerMails))
                    {
                        $documentationMail = new DocumentationMail($courseInfo, get_option('cbse_auto_print_options'));
                        $documentationMail->sentToPrinter(array_column($printerMails, 'mail'));
                    }

                }
            }
        } catch (Exception $e)
        {
            Analog::alert(get_class($this) . ' - ' . __FUNCTION__ . ' - ' . $course->course_id . ' - ' . $course->date);
            Analog::alert($e);
            $this->informAdmin($e, $course, __('Fatal error in the cronjob with the auto print documentation', CBSE_LANGUAGE_DOMAIN));
        }
    }

    private function getPrinterMailAddresses(CourseInfoDate $course): array
    {
        $printOptionsEmails = get_option('cbse_auto_print_options')['emails'];
        $eventTagIds = ArrayHelper::column($course->getEventTags(), 'term_id');

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