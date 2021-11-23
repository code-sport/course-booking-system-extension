<?php

namespace CBSE;

abstract class Mail
{
    public function __construct()
    {
    }

    abstract public function sent(): bool;
}
