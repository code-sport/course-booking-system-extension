<?php

namespace CBSE\Database;

class DatabaseBase
{
    protected function datebaseTableName($name): string
    {
        global $wpdb;
        return $wpdb->prefix . $name;
    }
}
