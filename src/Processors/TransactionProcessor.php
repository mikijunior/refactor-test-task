<?php

declare(strict_types=1);

namespace App\Processors;

use App\Contracts\DataProviderInterface;
use App\Output\CommissionFormatter;
use App\Services\CommissionCalculator;
use Exception;

class TransactionProcessor
{
    private CommissionCalculator $calculator;
    private DataProviderInterface $dataProvider;
    private CommissionFormatter $commissionFormatter;

    public function __construct(
        CommissionCalculator $calculator,
        DataProviderInterface $dataProvider,
        CommissionFormatter $commissionFormatter
    ) {
        $this->calculator = $calculator;
        $this->dataProvider = $dataProvider;
        $this->commissionFormatter = $commissionFormatter;
    }

    public function run(): void
    {
        foreach ($this->dataProvider->getData() as $transaction) {
            try {
                $commission = $this->calculator->calculateCommission($transaction);
                echo $this->commissionFormatter->prettify($commission) . PHP_EOL;
            } catch (Exception $e) {
                echo "Error processing transaction: " . $e->getMessage() . PHP_EOL;
            }
        }
    }
}
