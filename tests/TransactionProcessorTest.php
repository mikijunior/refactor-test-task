<?php

declare(strict_types=1);

namespace App\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use App\Processors\TransactionProcessor;
use App\Services\CommissionCalculator;
use App\DTO\Transaction;
use App\Providers\FileDataProvider;
use Generator;

class TransactionProcessorTest extends TestCase
{
    public function testProcessesMultipleTransactions(): void
    {
        $mockCalculator = $this->createMock(CommissionCalculator::class);
        $mockCalculator->method('calculateCommission')
            ->willReturnOnConsecutiveCalls('0.05', '0.10', '0.15');

        $transactions = [
            new Transaction(['bin' => '123456', 'amount' => 100.0, 'currency' => 'EUR']),
            new Transaction(['bin' => '234567', 'amount' => 200.0, 'currency' => 'USD']),
            new Transaction(['bin' => '345678', 'amount' => 300.0, 'currency' => 'GBP'])
        ];

        $mockDataProvider = $this->createMock(FileDataProvider::class);
        $mockDataProvider->method('getData')->willReturn((function () use ($transactions) {
            foreach ($transactions as $transaction) {
                yield $transaction;
            }
        })());

        $processor = new TransactionProcessor($mockCalculator, $mockDataProvider);

        ob_start();
        $processor->run();
        $output = ob_get_clean();

        $expectedOutput = implode("\n", array_map(function ($value) {
                return number_format($value, 2);
        }, [0.05, 0.10, 0.15])) . "\n";

        $this->assertEquals($expectedOutput, $output);
    }

    public function testErrorHandlingInProcessing(): void
    {
        $mockCalculator = $this->createMock(CommissionCalculator::class);
        $mockCalculator->method('calculateCommission')
            ->will($this->onConsecutiveCalls('0.05', $this->throwException(new Exception("Invalid data")), '0.15'));

        $transactions = [
            new Transaction(['bin' => '123456', 'amount' => 100.0, 'currency' => 'EUR']),
            new Transaction(['bin' => 'invalid', 'amount' => 200.0, 'currency' => 'USD']),
            new Transaction(['bin' => '345678', 'amount' => 300.0, 'currency' => 'GBP'])
        ];

        $mockDataProvider = $this->createMock(FileDataProvider::class);
        $mockDataProvider->method('getData')->willReturn((function () use ($transactions) {
            foreach ($transactions as $transaction) {
                yield $transaction;
            }
        })());

        $processor = new TransactionProcessor($mockCalculator, $mockDataProvider);

        ob_start();
        $processor->run();
        $output = ob_get_clean();

        $expectedOutput = "0.05\nError processing transaction: Invalid data\n0.15\n";
        $this->assertEquals($expectedOutput, $output);
    }

    public function testSkipsMalformedDataGracefully(): void
    {
        $mockCalculator = $this->createMock(CommissionCalculator::class);
        $mockCalculator->method('calculateCommission')
            ->willReturnCallback(function ($transaction) {
                if ($transaction instanceof Transaction) {
                    return '0.05';
                }
                throw new TypeError('Expected instance of Transaction');
            });

        $transactions = [
            new Transaction(['bin' => '123456', 'amount' => 100.0, 'currency' => 'EUR']),
            [],
            new Transaction(['bin' => '345678', 'amount' => 300.0, 'currency' => 'GBP'])
        ];

        $mockDataProvider = $this->createMock(FileDataProvider::class);
        $mockDataProvider->method('getData')->willReturn((function () use ($transactions) {
            foreach ($transactions as $transaction) {
                if ($transaction instanceof Transaction) {
                    yield $transaction;
                }
            }
        })());

        $processor = new TransactionProcessor($mockCalculator, $mockDataProvider);

        ob_start();
        $processor->run();
        $output = ob_get_clean();

        $expectedOutput = "0.05\n0.05\n";
        $this->assertEquals($expectedOutput, $output);
    }
}
