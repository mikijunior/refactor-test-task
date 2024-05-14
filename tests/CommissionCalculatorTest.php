<?php

declare(strict_types=1);

namespace App\Tests;

use App\Contracts\BinProviderInterface;
use App\Contracts\ExchangeRateProviderInterface;
use App\DTO\Transaction;
use App\Services\CommissionCalculator;
use App\Services\CommissionRate;
use App\Services\EuCountriesSpecification;
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

        $rateService = new CommissionRate($this->mockBinProvider, '0.02');
        $rateService->addSpecification(new EuCountriesSpecification());

        $this->calculator = new CommissionCalculator($this->mockExchangeProvider, $rateService, 2);
    }

    public function testCalculatesCommissionForNonEUTransactions(): void
    {
        $this->mockExchangeProvider->method('getRate')->willReturn('1.0');
        $this->mockBinProvider->method('getCountryCode')->willReturn('US');

        $transaction = new Transaction(['bin' => '234567', 'amount' => '100.0', 'currency' => 'USD']);
        $commission = $this->calculator->calculateCommission($transaction);

        $this->assertEquals('2.00', $commission);
    }

    public function testCalculatesZeroAmountTransaction(): void
    {
        $this->mockExchangeProvider->method('getRate')->willReturn('1.0');
        $this->mockBinProvider->method('getCountryCode')->willReturn('FR');

        $transaction = new Transaction(['bin' => '345678', 'amount' => '0.0', 'currency' => 'EUR']);
        $commission = $this->calculator->calculateCommission($transaction);

        $this->assertEquals('0.00', $commission);
    }

    public function testHandlesUnusualExchangeRates(): void
    {
        $this->mockExchangeProvider->method('getRate')->willReturn('0.5');
        $this->mockBinProvider->method('getCountryCode')->willReturn('FR');

        $transaction = new Transaction(['bin' => '456789', 'amount' => '200.0', 'currency' => 'Foreign']);
        $commission = $this->calculator->calculateCommission($transaction);

        $this->assertEquals('4.00', $commission);
    }

    public function testHandleZeroExchangeRate(): void
    {
        $this->mockExchangeProvider->method('getRate')->willReturn('0.00000000');
        $this->mockBinProvider->method('getCountryCode')->willReturn('US');

        $transaction = new Transaction(['bin' => '123456', 'amount' => '100.0', 'currency' => 'USD']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Exchange rate cannot be zero");

        $this->calculator->calculateCommission($transaction);
    }
}
