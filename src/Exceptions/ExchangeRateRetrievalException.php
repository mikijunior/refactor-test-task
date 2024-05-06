<?php

namespace App\Exceptions;

use Exception;

class ExchangeRateRetrievalException extends Exception
{
    public static function retrievalError(): ExchangeRateRetrievalException
    {
        return new self('Could not retrieve exchange rate data.');
    }

    public static function invalidJsonError(): ExchangeRateRetrievalException
    {
        return new self('Invalid JSON in exchange rate data.');
    }
}
