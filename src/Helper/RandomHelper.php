<?php

namespace CBSE\Helper;

use Exception;

final class RandomHelper
{
    /**
     * @throws Exception
     */
    public static function generateString(string $input, int $strength = 16): string
    {
        $input_length = strlen($input);
        $random_string = '';
        for ($i = 0; $i < $strength; $i++)
        {
            $random_character = $input[random_int(0, $input_length - 1)];
            $random_string .= $random_character;
        }

        return $random_string;
    }

    /**
     * @throws Exception
     */
    public static function secureString($length): string
    {
        $random_string = '';
        for ($i = 0; $i < $length; $i++)
        {
            $number = random_int(0, 36);
            $character = base_convert($number, 10, 36);
            $random_string .= $character;
        }

        return $random_string;
    }
}