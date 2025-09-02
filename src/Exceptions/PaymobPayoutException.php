<?php

namespace MohamedSaid\PaymobPayout\Exceptions;

use Exception;

class PaymobPayoutException extends Exception
{
    protected string $statusCode;

    public function __construct(string $message, string $statusCode = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): string
    {
        return $this->statusCode;
    }
}
