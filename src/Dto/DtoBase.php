<?php

namespace CBSE\Dto;

class DtoBase
{
    protected function datebaseTableName($name): string
    {
        global $wpdb;
        return $wpdb->prefix . $name;
    }
}
