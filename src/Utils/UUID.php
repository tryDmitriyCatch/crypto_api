<?php

namespace App\Utils;

/**
 * Class UUID
 */
class UUID
{
    public const HYPHEN = '-';

    /**
     * @return string
     */
    public static function generate(): string
    {
        $charId = md5(uniqid(mt_rand(), true));

        return substr($charId, 0, 8).static::HYPHEN
            .substr($charId, 8, 4).static::HYPHEN
            .substr($charId, 12, 4).static::HYPHEN
            .substr($charId, 16, 4).static::HYPHEN
            .substr($charId, 20, 12);
    }
}
