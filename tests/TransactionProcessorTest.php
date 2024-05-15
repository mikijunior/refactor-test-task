<?php

declare(strict_types=1);

namespace App\Tests;

use App\DTO\Transaction;
use App\Output\CommissionFormatter;
use App\Processors\TransactionProcessor;
use App\Providers\FileDataProvider;
use App\Services\CommissionCalculator;
use Exception;
use Generator;
use PHPUnit\Framework\TestCase;
use TypeError;

class TransactionProcessorTest extends TestCase
{
    private $mockCalculator;
    private $mockDataProvider;
    private TransactionProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockCalculator = $this->createMock(CommissionCalculator::class);
        $this->mockDataProvider = $this->createMock(FileDataProvider::class);
        $commissionFormatter = new CommissionFormatter(2);

        $this->processor = new TransactionProcessor(
            $this->mockCalculator,
            $this->mockDataProvider,
            $commissionFormatter
        );
    }

    public function testProcessesMultipleTransactions(): void
    {
        $this->mockCalculator->method('calculateCommission')
            ->willReturnOnConsecutiveCalls('0.05', '0.10', '0.15');

        $transactions = [
            new Transaction('123456', '100.0', 'EUR'),
            new Transaction('234567', '200.0', 'USD'),
            new Transaction('345678', '300.0', 'GBP')
        ];

        $this->mockDataProvider->method('getData')->willReturn((function () use ($transactions) {
            foreach ($transactions as $transaction) {
                yield $transaction;
            }
        })());

        ob_start();
        $this->processor->run();
        $output = ob_get_clean();

        $expectedOutput = implode("\n", array_map(function ($value) {
                return number_format($value, 2);
        }, [0.05, 0.10, 0.15])) . "\n";

        $this->assertEquals($expectedOutput, $output);
    }

    public function testErrorHandlingInProcessing(): void
    {
        $this->mockCalculator->method('calculateCommission')
            ->will($this->onConsecutiveCalls('0.05', $this->throwException(new Exception("Invalid data")), '0.15'));

        $transactions = [
            new Transaction('123456', '100.0', 'EUR'),
            new Transaction('invalid', '200.0', 'USD'),
            new Transaction('345678', '300.0', 'GBP')
        ];

        $this->mockDataProvider->method('getData')->willReturn((function () use ($transactions) {
            foreach ($transactions as $transaction) {
                yield $transaction;
            }
        })());

        ob_start();
        $this->processor->run();
        $output = ob_get_clean();

        $expectedOutput = "0.05\nError processing transaction: Invalid data\n0.15\n";
        $this->assertEquals($expectedOutput, $output);
    }

    public function testSkipsMalformedDataGracefully(): void
    {
        $this->mockCalculator->method('calculateCommission')
            ->willReturnCallback(function ($transaction) {
                if ($transaction instanceof Transaction) {
                    return '0.05';
                }
                throw new TypeError('Expected instance of Transaction');
            });

        $transactions = [
            new Transaction('123456', '100.0', 'EUR'),
            [],
            new Transaction('345678', '300.0', 'GBP')
        ];

        $this->mockDataProvider->method('getData')->willReturn((function () use ($transactions) {
            foreach ($transactions as $transaction) {
                if ($transaction instanceof Transaction) {
                    yield $transaction;
                }
            }
        })());

        ob_start();
        $this->processor->run();
        $output = ob_get_clean();

        $expectedOutput = "0.05\n0.05\n";
        $this->assertEquals($expectedOutput, $output);
    }
}
