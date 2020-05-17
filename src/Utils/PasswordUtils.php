<?php

namespace App\Utils;

/**
 * Class PasswordUtils
 */
class PasswordUtils
{
    public static function hashPassword($password): string
    {
        return md5($password);
    }
}
