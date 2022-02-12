<?php

namespace CBSE;


use Analog;
use CBSE\Cron\LoggingCleanUp;
use CBSE\Helper\PathHelper;

final class Logging
{
    public static function init()
    {
        $folder = self::getFolder();
        $logFile = PathHelper::combine($folder, date('Y-m-d', time()) . '.log');
        if (!is_dir($folder))
        {
            if (!mkdir($folder, 0777, true))
            {
                trigger_error('Could\'t generate folder: ' . $folder, E_USER_WARNING);
            }
        }
        if (!file_exists($logFile))
        {
            if (!touch($logFile))
            {
                trigger_error('Log file ' . $logFile . ' could\'t be created.', E_USER_ERROR);
            }
        }
        Analog::handler(Analog\Handler\File::init($logFile));

        LoggingCleanUp::getInstance();
    }

    public static function getFolder(): string
    {
        $pluginDir = plugin_dir_path(CBSE_PLUGIN_BASE_FILE);
        $folderPath = PathHelper::combine($pluginDir, '..', '..', 'cbse', 'logs');
        return PathHelper::realPath($folderPath);
    }
}

