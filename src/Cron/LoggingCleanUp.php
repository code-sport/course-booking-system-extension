<?php

namespace CBSE\Cron;


use CBSE\Exception\UnserializeSingletonException;
use CBSE\Logging;

class LoggingCleanUp
{
    private static ?LoggingCleanUp $instance = null;
    private string $hook;
    private string $file;

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     *
     * @param string $file*/
    private function __construct(string $file)
    {
        $this->hook = 'cbse_cleanup_logs';
        add_filter('cron_schedules', [$this, 'addCronDailyInterval']);
        add_action($this->hook, [$this, 'dailyExec']);

        if (!wp_next_scheduled($this->hook))
        {
            wp_schedule_event(time(), 'daily', $this->hook);
        }
        $this->file = $file;}

    /**
     * gets the instance via lazy initialization (created on first usage)
     */
    public static function getInstance($file): LoggingCleanUp
    {
        if (static::$instance === null)
        {
            static::$instance = new LoggingCleanUp($file);
        }

        return static::$instance;
    }

    public function addCronDailyInterval($schedules)
    {
        $schedules['daily'] = array('interval' => 86400, 'display' => esc_html__('Daily'),);
        return $schedules;
    }

    public function dailyExec()
    {
        // 30 Tag
        $deleteTime = 60 * 60 * 24 * 30;
        $loggingFolder = Logging::getFolder($this->file);
        $logFiles = array_diff(scandir($loggingFolder), array('.', '..'));
        $now = time();

        foreach ($logFiles as $logFile)
        {
            if (is_file($logFile) && $now - filemtime($logFile) >= $deleteTime)
            {
                unlink($logFile);
            }
        }
    }


    /**
     * prevent from being unserialized (which would create a second instance of it)
     *
     * @throws UnserializeSingletonException
     */
    public function __wakeup()
    {
        throw new UnserializeSingletonException();
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }

}
