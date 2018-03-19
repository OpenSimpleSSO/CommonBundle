<?php

namespace SimpleSSO\CommonBundle\Exception;

use RuntimeException;
use Throwable;

/**
 * An OpenSSL error occurred.
 */
class OpenSslException extends RuntimeException
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        $opensslError = openssl_error_string();
        if ($opensslError) {
            $message .= ' ' . $opensslError;
        }
        parent::__construct($message, $code, $previous);
    }
}
