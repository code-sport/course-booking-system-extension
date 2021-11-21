<?php

namespace CBSE;

abstract class Mail
{
    public function __construct()
    {
    }

    public abstract function sent(): bool;
}
