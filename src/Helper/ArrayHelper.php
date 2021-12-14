<?php

namespace CBSE\Helper;

final class ArrayHelper
{
    public static function excludeAndColumn(array $array, string $exclude, string $filter): array
    {
        $excludes = explode(',', $exclude);
        $arrayFiltered = array_filter($array, function ($val) use ($excludes)
        {
            // Fatal error: Uncaught Error: Cannot use object of type WP_Term as array
            $values = $val->to_array();
            $id = $values['term_id'];
            return (!in_array($id, $excludes));
        });
        return array_column($arrayFiltered, $filter);
    }

    public static function column(array $array, string $filter): array
    {
        return array_column($array, $filter);
    }
}
