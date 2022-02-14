<?php

namespace CBSE\Helper;

use DateTimeZone;

final class DateTimeZoneHelper
{
    /**
     * Will load the time zone from wordpress and convert it to DateTimeZone
     *
     * @return DateTimeZone
     */
    public static function fromWordPress(): DateTimeZone
    {
        return wp_timezone();
    }
}
