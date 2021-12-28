<?php

namespace CBSE\Admin\User\Dto;

class DtoBase
{
    protected function datebaseTableName($name): string
    {
        global $wpdb;
        return $wpdb->prefix . $name;
    }
}
