<?php

namespace App\Exceptions;

use Exception;

class BinDataRetrievalException extends Exception
{
    public static function retrievalError(): BinDataRetrievalException
    {
        return new self('Could not retrieve BIN data.');
    }

    public static function invalidJsonError(): BinDataRetrievalException
    {
        return new self('Invalid JSON in BIN data.');
    }
}
