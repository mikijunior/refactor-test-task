<?php

declare(strict_types=1);

namespace App\Processors;

use App\Contracts\DataProviderInterface;
use App\Services\CommissionCalculator;
use Generator;
use Exception;

class TransactionProcessor
{
    private CommissionCalculator $calculator;
    private DataProviderInterface $dataProvider;

    public function __construct(CommissionCalculator $calculator, DataProviderInterface $dataProvider)
    {
        $this->calculator = $calculator;
        $this->dataProvider = $dataProvider;
    }

    public function run()
    {
        foreach ($this->dataProvider->getData() as $transaction) {
            try {
                $result = $this->calculator->calculateCommission($transaction);
                echo $result . PHP_EOL;
            } catch (Exception $e) {
                echo "Error processing transaction: " . $e->getMessage() . PHP_EOL;
            }
        }
    }
}
