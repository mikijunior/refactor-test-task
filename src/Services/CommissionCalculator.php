<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\BinProviderInterface;
use App\Contracts\ExchangeRateProviderInterface;
use App\DTO\Transaction;
use RuntimeException;

class CommissionCalculator
{
    private BinProviderInterface $binProvider;
    private ExchangeRateProviderInterface $exchangeRateProvider;
    private CommissionRateService $rateSpecification;

    public function __construct(
        BinProviderInterface $binProvider,
        ExchangeRateProviderInterface $exchangeRateProvider,
        CommissionRateService $rateSpecification
    ) {
        $this->binProvider = $binProvider;
        $this->exchangeRateProvider = $exchangeRateProvider;
        $this->rateSpecification = $rateSpecification;
    }

    public function calculateCommission(Transaction $transaction): string
    {
        $countryCode = $this->binProvider->getCountryCode($transaction->getBin());
        $rate = $this->exchangeRateProvider->getRate($transaction->getCurrency());

        if ($rate == 0) {
            throw new RuntimeException("Exchange rate cannot be zero");
        }

        $amntFixed = bcdiv((string)$transaction->getAmount(), (string)$rate, 2);
        $commissionRate = $this->rateSpecification->getCommissionRate($countryCode);
        $commission = bcmul($amntFixed, $commissionRate, 2);

        return bcdiv((string)ceil((float)bcmul($commission, '100')), '100', 2);
    }
}
