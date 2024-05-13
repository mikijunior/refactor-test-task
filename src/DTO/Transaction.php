<?php

declare(strict_types=1);

namespace App\DTO;

use App\Exceptions\InvalidTransactionFormatException;

class Transaction
{
    private $bin;
    private $amount;
    private $currency;

    public const REQUIRED_FIELDS = [
        'bin',
        'amount',
        'currency',
    ];

    /**
     * @throws InvalidTransactionFormatException
     */
    public function __construct(array $transaction)
    {
        $this->validateTransaction($transaction);

        $this->bin = $transaction['bin'];
        $this->amount = (string)$transaction['amount'];
        $this->currency = $transaction['currency'];
    }

    public function getBin(): string
    {
        return $this->bin;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @throws InvalidTransactionFormatException
     */
    private function validateTransaction(array $transaction)
    {
        foreach (self::REQUIRED_FIELDS as $field) {
            if (!array_key_exists($field, $transaction)) {
                throw InvalidTransactionFormatException::missingFields();
            }
        }
    }
}
