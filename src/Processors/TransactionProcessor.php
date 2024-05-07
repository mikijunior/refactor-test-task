<?php

declare(strict_types=1);

namespace App\Processors;

use App\Contracts\DataProviderInterface;
use App\DTO\Transaction;
use App\Services\CommissionCalculator;
use Exception;
use Generator;

class TransactionProcessor
{
    private CommissionCalculator $calculator;

    public function __construct(CommissionCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Processes all transactions from the data provider.
     *
     * @param DataProviderInterface $provider
     * @return Generator
     */
    public function process(DataProviderInterface $provider): Generator
    {
        foreach ($provider->getData() as $transactionData) {
            try {
                $transaction = new Transaction($transactionData);
                $commission = $this->calculator->calculateCommission($transaction);
                yield $commission;
            } catch (Exception $e) {
                yield "Error: " . $e->getMessage();
            }
        }
    }
}
