<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Services\CommissionCalculator;
use App\Services\CommissionRateService;
use App\Services\EuCountriesSpecification;
use App\Contracts\ExchangeRateProviderInterface;
use App\Contracts\BinProviderInterface;
use App\DTO\Transaction;

class CommissionCalculatorTest extends TestCase
{
    public function testCalculatesCommissionForNonEUTransactions()
    {
        $mockExchangeProvider = $this->createMock(ExchangeRateProviderInterface::class);
        $mockExchangeProvider->method('getRate')->willReturn('1.0');

        $mockBinProvider = $this->createMock(BinProviderInterface::class);
        $mockBinProvider->method('getCountryCode')->willReturn('US');

        $rateService = new CommissionRateService($mockBinProvider, '0.02');
        $calculator = new CommissionCalculator($mockExchangeProvider, $rateService);

        $transaction = new Transaction(['bin' => '234567', 'amount' => '100.0', 'currency' => 'USD']);
        $commission = $calculator->calculateCommission($transaction);

        $this->assertEquals('2.00', $commission);
    }

    public function testCalculatesZeroAmountTransaction()
    {
        $mockExchangeProvider = $this->createMock(ExchangeRateProviderInterface::class);
        $mockExchangeProvider->method('getRate')->willReturn('1.0');

        $mockBinProvider = $this->createMock(BinProviderInterface::class);
        $mockBinProvider->method('getCountryCode')->willReturn('FR');

        $rateService = new CommissionRateService($mockBinProvider, '0.01');
        $calculator = new CommissionCalculator($mockExchangeProvider, $rateService);

        $transaction = new Transaction(['bin' => '345678', 'amount' => '0.0', 'currency' => 'EUR']);
        $commission = $calculator->calculateCommission($transaction);

        $this->assertEquals('0.00', $commission);
    }

    public function testHandlesUnusualExchangeRates()
    {
        $mockExchangeProvider = $this->createMock(ExchangeRateProviderInterface::class);
        $mockExchangeProvider->method('getRate')->willReturn('0.5');

        $mockBinProvider = $this->createMock(BinProviderInterface::class);
        $mockBinProvider->method('getCountryCode')->willReturn('FR');

        $rateService = new CommissionRateService($mockBinProvider, '0.01');
        $calculator = new CommissionCalculator($mockExchangeProvider, $rateService);

        $transaction = new Transaction(['bin' => '456789', 'amount' => '200.0', 'currency' => 'Foreign']);
        $commission = $calculator->calculateCommission($transaction);

        $this->assertEquals('4.00', $commission);
    }
}
