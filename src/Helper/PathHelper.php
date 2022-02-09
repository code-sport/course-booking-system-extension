<?php
/***
 *    https://stackoverflow.com/a/61723413/14815278
 */

namespace CBSE\Helper;

use Analog;

class PathHelper
{
    public static function combine(): string
    {
        $paths = func_get_args();
        $paths = array_map(fn($path) => str_replace(["\\", '/'], DIRECTORY_SEPARATOR, $path), $paths);
        $paths = array_map(fn($path) => self::trimPath($path), $paths);
        return implode(DIRECTORY_SEPARATOR, $paths);
    }

    private static function trimPath(string $path): string
    {
        $path = trim($path);
        $start = $path[0] === DIRECTORY_SEPARATOR ? 1 : 0;
        $end = $path[strlen($path) - 1] === DIRECTORY_SEPARATOR ? -1 : strlen($path);
        return substr($path, $start, $end);
    }

    /***
     * See https://stackoverflow.com/a/10488552/14815278
     *
     * @param string $path
     *
     * @return string
     */
    public static function realPath(string $path): string
    {
        $from = $path;
        while ($to = realpath($from) and $to !== $from)
        {
            $from = $to;
        }
        if (!$to)
        {
            Analog::debug('PathHelper::realPath | ' . $path);
            return $path;
        }
        Analog::debug('PathHelper::realPath | ' . $path . ' => ' . $to);
        return $to;
    }
}
