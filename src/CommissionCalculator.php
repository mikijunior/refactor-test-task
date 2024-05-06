<?php

declare(strict_types=1);

namespace App;

use App\Contracts\BinProviderInterface;
use App\Contracts\ExchangeRateProviderInterface;
use App\DTO\Transaction;

class CommissionCalculator
{
    private $binProvider;
    private $exchangeRateProvider;

    public function __construct(BinProviderInterface $binProvider, ExchangeRateProviderInterface $exchangeRateProvider)
    {
        $this->binProvider = $binProvider;
        $this->exchangeRateProvider = $exchangeRateProvider;
    }

    public function calculateCommission(Transaction $transaction): float
    {
        $countryCode = $this->binProvider->getCountryCode($transaction->getBin());
        $isEu = $this->isEu($countryCode);

        $rate = $this->exchangeRateProvider->getRate($transaction->getCurrency());
        $amntFixed = $transaction->getAmount() / $rate;

        $commission = $amntFixed * ($isEu ? 0.01 : 0.02);

        return ceil($commission * 100) / 100;
    }

    private function isEu(string $countryCode): bool
    {
        return in_array($countryCode, self::getEuCountries(), true);
    }

    private static function getEuCountries(): array
    {
        return [
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR',
            'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PO',
            'PT', 'RO', 'SE', 'SI', 'SK',
        ];
    }
}
