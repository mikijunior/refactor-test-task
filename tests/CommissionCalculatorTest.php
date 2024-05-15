<?php

declare(strict_types=1);

namespace App\Tests;

use App\Contracts\BinProviderInterface;
use App\Contracts\ExchangeRateProviderInterface;
use App\DTO\Transaction;
use App\Services\CommissionCalculator;
use App\Services\CommissionRate;
use App\Services\EuCountriesSpecification;
use App\Services\Math;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CommissionCalculatorTest extends TestCase
{
    private $mockExchangeProvider;
    private $mockBinProvider;
    private CommissionCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockExchangeProvider = $this->createMock(ExchangeRateProviderInterface::class);
        $this->mockBinProvider = $this->createMock(BinProviderInterface::class);

        $math = new Math(2);

        $rateService = new CommissionRate($this->mockBinProvider, '0.02');
        $rateService->addSpecification(new EuCountriesSpecification());

        $this->calculator = new CommissionCalculator($this->mockExchangeProvider, $rateService, $math);
    }

    public function testCalculatesCommissionForNonEUTransactions(): void
    {
        $this->mockExchangeProvider->method('getRate')->willReturn('1.0');
        $this->mockBinProvider->method('getCountryCode')->willReturn('US');

        $transaction = new Transaction('234567', '100.0', 'USD');
        $commission = $this->calculator->calculateCommission($transaction);

        $this->assertEquals('2.00', $commission);
    }

    public function testCalculatesZeroAmountTransaction(): void
    {
        $this->mockExchangeProvider->method('getRate')->willReturn('1.0');
        $this->mockBinProvider->method('getCountryCode')->willReturn('FR');

        $transaction = new Transaction('345678', '0.0', 'EUR');
        $commission = $this->calculator->calculateCommission($transaction);

        $this->assertEquals('0.00', $commission);
    }

    public function testHandlesUnusualExchangeRates(): void
    {
        $this->mockExchangeProvider->method('getRate')->willReturn('0.5');
        $this->mockBinProvider->method('getCountryCode')->willReturn('FR');

        $transaction = new Transaction('456789', '200.0', 'Foreign');
        $commission = $this->calculator->calculateCommission($transaction);

        $this->assertEquals('4.00', $commission);
    }

    public function testHandleZeroExchangeRate(): void
    {
        $this->mockExchangeProvider->method('getRate')->willReturn('0.00000000');
        $this->mockBinProvider->method('getCountryCode')->willReturn('US');

        $transaction = new Transaction('123456', '100.0', 'USD');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Exchange rate cannot be zero");

        $this->calculator->calculateCommission($transaction);
    }
}
