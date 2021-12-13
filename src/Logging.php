<?php

namespace CBSE;


use Analog;
use CBSE\Cron\LoggingCleanUp;

final class Logging
{
    public static function init()
    {
        $folder = self::getFolder();
        $logFile = join(DIRECTORY_SEPARATOR, array($folder, date('Y-m-d', time()) . '.log'));
        $folder = dirname($logFile);
        if (!file_exists($folder))
        {
            mkdir($folder, 0777, true);
        }
        Analog::handler(Analog\Handler\File::init($logFile));

        LoggingCleanUp::getInstance();
    }

    public static function getFolder(): string
    {
        return realpath(join(DIRECTORY_SEPARATOR, array(plugin_dir_path(__FILE__), '..', 'logs')));
    }
}

