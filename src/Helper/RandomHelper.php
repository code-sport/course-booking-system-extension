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
        $inputLength = strlen($input);
        $randomString = '';
        for ($i = 0; $i < $strength; $i++)
        {
            $randomCharacter = $input[random_int(0, $inputLength - 1)];
            $randomString .= $randomCharacter;
        }

        return $randomString;
    }

    /**
     * @throws Exception
     */
    public static function secureString($length): string
    {
        $randomString = '';
        for ($i = 0; $i < $length; $i++)
        {
            $number = random_int(0, 36);
            $character = base_convert($number, 10, 36);
            $randomString .= $character;
        }

        return $randomString;
    }
}
