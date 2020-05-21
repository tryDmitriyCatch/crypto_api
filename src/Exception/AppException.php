<?php

namespace App\Exception;

use Exception;

class AppException extends Exception
{
    public const EXCEPTION_USER_NOT_FOUND = 'User was not found';
    public const EXCEPTION_DECODING_ERROR = 'Content-type cannot be decoded';
    public const EXCEPTION_TOO_MANY_REDIRECTS = 'Too many redirects encountered';
    public const EXCEPTION_SERVER_ERROR = 'Server error';
    public const EXCEPTION_TRANSPORT_ERROR = 'Transport error';

    /**
     * {@inheritdoc}
     */
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
