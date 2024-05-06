<?php

namespace App\Exceptions;

use App\DTO\Transaction;
use Exception;

class InvalidTransactionFormatException extends Exception
{
    public static function invalidJson(): InvalidTransactionFormatException
    {
        return new self('Invalid JSON format in transaction data.');
    }

    public static function missingFields(): InvalidTransactionFormatException
    {
        return new self(
            'Transaction data is missing required fields. Required fields: ' .
            implode(', ', Transaction::REQUIRED_FIELDS)
        );
    }
}
