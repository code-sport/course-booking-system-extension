<?php

namespace CBSE\Exception;

use Exception;

class UnserializeSingletonException extends Exception
{
    public function __construct()
    {
        parent::__construct("Cannot unserialize singleton");
    }
}
