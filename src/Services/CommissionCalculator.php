<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ExchangeRateProviderInterface;
use App\DTO\Transaction;
use App\Providers\ExchangeRateProvider;
use RuntimeException;

class CommissionCalculator
{
    private ExchangeRateProviderInterface $exchangeRateProvider;
    private CommissionRate $commissionRate;
    private Math $math;

    public function __construct(
        ExchangeRateProviderInterface $exchangeRateProvider,
        CommissionRate $commissionRate,
        Math $math
    ) {
        $this->exchangeRateProvider = $exchangeRateProvider;
        $this->commissionRate = $commissionRate;
        $this->math = $math;
    }

    public function calculateCommission(Transaction $transaction): string
    {
        $rate = $this->exchangeRateProvider->getRate($transaction->getCurrency());
        $amount = $transaction->getAmount();

        if ($this->math->compare($rate, '0') === 0) {
            throw new RuntimeException("Exchange rate cannot be zero");
        }

        $convertedAmount = $this->math->divide($amount, $rate);
        $commissionRate = $this->commissionRate->getCommissionRate($transaction->getBin());
        $commission = $this->math->multiply($convertedAmount, $commissionRate);

        return $this->math->divide(
            (string)ceil(
                (float)$this->math->multiply(
                    $commission,
                    '100'
                )
            ),
            '100'
        );
    }
}
