<?php

declare(strict_types=1);

namespace App\DTO;

class Transaction
{
    private string $bin;
    private string $amount;
    private string $currency;

    public function __construct(string $bin, string $amount, string $currency)
    {
        $this->bin = $bin;
        $this->amount = $amount;
        $this->currency = $currency;
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
}
