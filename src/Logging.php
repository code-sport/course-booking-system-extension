<?php

namespace CBSE;


use Analog;
use CBSE\Admin\User\Cron\LoggingCleanUp;

final class Logging
{
    public static function init(string $file)
    {
        $folder = self::getFolder($file);
        $logFile = implode(DIRECTORY_SEPARATOR, array($folder, date('Y-m-d', time()) . '.log'));
        $folder = dirname($logFile);
        if (!file_exists($folder))
        {
            mkdir($folder, 0777, true);
        }
        Analog::handler(Analog\Handler\File::init($logFile));

        LoggingCleanUp::getInstance();
    }

    public static function getFolder(string $file): string
    {
        $pluginDir = plugin_dir_path($file);
        $folderPath = implode(DIRECTORY_SEPARATOR, array($pluginDir, 'logs'));
        $folderRealPath = realpath($folderPath);
        if ($folderRealPath !== false)
        {
            return $folderRealPath;
        }
        return $folderPath;
    }
}

