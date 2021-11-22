<?php

namespace CBSE\Helper;

final class ArrayHelper
{
    public static function excludeAndColumn(array $array, string $exclude, string $filter)
    {
        $excludes = explode(',', $exclude);
        $array_filtered = array_filter($array, function ($val) use ($excludes)
        {
            // Fatal error: Uncaught Error: Cannot use object of type WP_Term as array
            $values = $val->to_array();
            $id = $values['term_id'];
            return (!in_array($id, $excludes));
        });
        return array_column($array_filtered, $filter);
    }
}