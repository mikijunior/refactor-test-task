<?php

declare(strict_types=1);

namespace App\Tests;

use App\DTO\Transaction;
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

        $this->processor = new TransactionProcessor($this->mockCalculator, $this->mockDataProvider);
    }

    public function testProcessesMultipleTransactions(): void
    {
        $this->mockCalculator->method('calculateCommission')
            ->willReturnOnConsecutiveCalls('0.05', '0.10', '0.15');

        $transactions = [
            new Transaction(['bin' => '123456', 'amount' => 100.0, 'currency' => 'EUR']),
            new Transaction(['bin' => '234567', 'amount' => 200.0, 'currency' => 'USD']),
            new Transaction(['bin' => '345678', 'amount' => 300.0, 'currency' => 'GBP'])
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
            new Transaction(['bin' => '123456', 'amount' => 100.0, 'currency' => 'EUR']),
            new Transaction(['bin' => 'invalid', 'amount' => 200.0, 'currency' => 'USD']),
            new Transaction(['bin' => '345678', 'amount' => 300.0, 'currency' => 'GBP'])
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
            new Transaction(['bin' => '123456', 'amount' => 100.0, 'currency' => 'EUR']),
            [],
            new Transaction(['bin' => '345678', 'amount' => 300.0, 'currency' => 'GBP'])
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
