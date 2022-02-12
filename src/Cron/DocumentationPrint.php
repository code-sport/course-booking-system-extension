<?php

namespace CBSE\Cron;

use Analog\Analog;
use CBSE\Database\CourseInfoDate;
use CBSE\Database\CoursesInTime;
use CBSE\DocumentationMail;
use CBSE\DocumentationPdf;
use CBSE\Helper\ArrayHelper;
use CBSE\Helper\PathHelper;
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
        Analog::log(get_class($this) . ' - ' . __FUNCTION__ . ' - Run on ' . count($courses) . ' courses');

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
                    $saveOnFolders = $this->getSaveOnFolders($courseInfo);

                    if (!empty($printerMails))
                    {
                        Analog::log(get_class($this) . ' - ' . __FUNCTION__ . ' - ' . $course->course_id . ' - ' . $course->date . ' - print on: ' . implode(', ', array_column($printerMails, 'mail')));
                        $documentationMail = new DocumentationMail($courseInfo, get_option('cbse_auto_print_options'));
                        $documentationMail->sentToPrinter(array_column($printerMails, 'mail'));
                    }

                    if (!empty($saveOnFolders))
                    {
                        Analog::log(get_class($this) . ' - ' . __FUNCTION__ . ' - ' . $course->course_id . ' - ' . $course->date . ' - save on: ' . implode(', ', $saveOnFolders));
                        $documentationPdf = new DocumentationPdf($courseInfo);
                        $documentationPdf->generatePdf();
                        foreach ($saveOnFolders as $saveOnFolder)
                        {
                            $src = $documentationPdf->getPdfFile();
                            $dest = $this->generateDestinationFileName($courseInfo, $saveOnFolder);
                            Analog::info('Copy from ' . $src . ' to ' . $dest);
                            if (copy($src, $dest))
                            {
                                Analog::warning('Documentation could not copied');
                            }
                        }
                        $documentationPdf->unlink();
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
        $eventTagIds = ArrayHelper::column($course->getEventTags(), 'term_id');
        Analog::debug('term ids for print: ' . implode(',', $eventTagIds));

        $emailAddresses = array();

        foreach ($eventTagIds as $eventTagId)
        {
            $emailAddresses[] = array('mail' => get_term_meta($eventTagId, 'cbse_auto_print_mail', true));
        }
        Analog::debug('getPrinterMailAddresses: ' . implode(',', $emailAddresses));
        return $emailAddresses;
    }

    private function getSaveOnFolders(CourseInfoDate $course): array
    {
        $eventTagIds = ArrayHelper::column($course->getEventTags(), 'term_id');
        Analog::debug('term ids for save in disk: ' . implode(',', $eventTagIds));

        $folders = array();
        foreach ($eventTagIds as $eventTagId)
        {
            if (get_term_meta($eventTagId, 'cbse_auto_print_folder', true) == 1)
            {
                $folders[] = $eventTagId;
            }
        }
        Analog::debug('getSaveOnFolders: ' . implode(',', $folders));
        return $folders;
    }

    /**
     * @param CourseInfoDate $course
     * @param string         $tagName
     *
     * @return string
     */
    private function generateDestinationFileName(CourseInfoDate $course, string $tagName): string
    {
        $fileName = $course->getCourseId() . '_' . $course->getCourseDate()->format('Y-m-d') . '.pdf';
        $path = PathHelper::realPath(PathHelper::combine(plugin_dir_path(CBSE_PLUGIN_BASE_FILE), '..', '..', 'cbse', 'auto-print', $tagName));
        if (!is_dir($path))
        {
            Analog::info('create folder: ' . $path);
            if (mkdir($path, 0777, true))
            {
                Analog::warning('Folder ' . $path . ' could not be created.');
            }
            //Add .htaccess so that no access from the internet is possible
            $htaccessSrc = PathHelper::combine(plugin_dir_path(CBSE_PLUGIN_BASE_FILE), 'templates', '.htaccess.txt');
            $htaccessDest = $path . DIRECTORY_SEPARATOR . '.htaccess';
            if (copy($htaccessSrc, $htaccessDest))
            {
                Analog::warning('.htaccess could not copied.');
            }
        }
        return PathHelper::realPath(PathHelper::combine($path, $fileName));
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }
}
