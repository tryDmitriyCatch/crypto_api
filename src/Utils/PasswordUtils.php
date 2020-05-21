<?php

namespace App\Utils;

/**
 * Class PasswordUtils
 */
class PasswordUtils
{
    /**
     * @param $password
     * @return string
     */
    public static function hashPassword($password): string
    {
        return md5($password);
    }
}
