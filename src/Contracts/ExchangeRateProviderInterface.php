<?php

declare(strict_types=1);

namespace App\Contracts;

interface ExchangeRateProviderInterface
{
    public function getRate(string $currency): string;
}
