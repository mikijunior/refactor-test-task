<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\BinProviderInterface;
use App\Contracts\ExchangeRateProviderInterface;
use App\DTO\Transaction;
use RuntimeException;

class CommissionCalculator
{
    private ExchangeRateProviderInterface $exchangeRateProvider;
    private CommissionRateService $rateSpecification;
    private int $precision;

    public function __construct(
        ExchangeRateProviderInterface $exchangeRateProvider,
        CommissionRateService $rateSpecification,
        int $precision = 2
    ) {
        $this->exchangeRateProvider = $exchangeRateProvider;
        $this->rateSpecification = $rateSpecification;
        $this->precision = $precision;
    }

    public function calculateCommission(Transaction $transaction): string
    {
        $rate = $this->exchangeRateProvider->getRate($transaction->getCurrency());

        if (bccomp($rate, '0.00000000', 8) === 0) {
            throw new RuntimeException("Exchange rate cannot be zero");
        }

        $convertedAmount = bcdiv($transaction->getAmount(), $rate, $this->precision);
        $commissionRate = $this->rateSpecification->getCommissionRate($transaction->getBin());
        $commission = bcmul($convertedAmount, $commissionRate, $this->precision);

        return bcdiv((string)ceil((float)bcmul($commission, '100')), '100', $this->precision);
    }
}
