<?php
/***
 *    https://stackoverflow.com/a/61723413/14815278
 */

namespace CBSE\Helper;

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
     * See https://www.php.net/manual/de/function.realpath.php#84012
     *
     * @param string $path
     *
     * @return string
     */
    public static function realPath(string $path): string
    {
        $pathString = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $pathString), 'strlen');
        $absolutes = array();
        foreach ($parts as $part)
        {
            if ('.' == $part)
            {
                continue;
            }
            if ('..' == $part)
            {
                array_pop($absolutes);
            }
            else
            {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }
}
