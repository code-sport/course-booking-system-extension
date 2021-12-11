<?php

namespace CBSE;

abstract class Mail
{
    public function __construct()
    {
    }

    abstract public function sentToUser(int $userId): bool;
}
