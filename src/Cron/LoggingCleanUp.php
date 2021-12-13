<?php

namespace CBSE\Cron;


use CBSE\Logging;
use Exception;

class LoggingCleanUp
{
    private static ?LoggingCleanUp $instance = null;
    private string $hook;

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct()
    {
        $this->hook = 'cbse_cleanup_logs';
        add_filter('cron_schedules', [$this, 'addCronDailyInterval']);
        add_action($this->hook, [$this, 'dailyExec']);

        if (!wp_next_scheduled($this->hook))
        {
            wp_schedule_event(time(), 'daily', $this->hook);
        }
    }

    /**
     * gets the instance via lazy initialization (created on first usage)
     */
    public static function getInstance(): LoggingCleanUp
    {
        if (static::$instance === null)
        {
            static::$instance = new LoggingCleanUp();
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
        $deleteTime = 60 * 60 * 24 * 30; // 30 Tag
        $loggingFOlder = Logging::getFolder();
        $logFiles = array_diff(scandir($loggingFOlder), array('.', '..'));
        $now = time();

        foreach ($logFiles as $logFile)
        {
            if (is_file($logFile))
            {
                if ($now - filemtime($logFile) >= $deleteTime)
                {
                    unlink($logFile);
                }
            }
        }
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }

}