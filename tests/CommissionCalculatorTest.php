<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Services\CommissionCalculator;
use App\Services\CommissionRateService;
use App\Services\CountryService;
use App\DTO\Transaction;
use App\Providers\BinListProvider;
use App\Providers\ExchangeRateProvider;

class CommissionCalculatorTest extends TestCase
{
    public function testCalculatesCommissionForNonEUTransactions(): void
    {
        $mockEuService = $this->createMock(CountryService::class);
        $mockEuService->method('isEuCountry')->willReturn(false);

        $mockBinProvider = $this->createMock(BinListProvider::class);
        $mockBinProvider->method('getCountryCode')->willReturn('US');

        $mockExchangeProvider = $this->createMock(ExchangeRateProvider::class);
        $mockExchangeProvider->method('getRate')->willReturn(1.0);

        $rateService = new CommissionRateService($mockEuService);
        $calculator = new CommissionCalculator($mockBinProvider, $mockExchangeProvider, $rateService);

        $transaction = new Transaction(['bin' => '234567', 'amount' => 100.0, 'currency' => 'USD']);
        $commission = $calculator->calculateCommission($transaction);

        $this->assertEquals('2.00', $commission);
    }

    public function testCalculatesZeroAmountTransaction(): void
    {
        $mockEuService = $this->createMock(CountryService::class);
        $mockEuService->method('isEuCountry')->willReturn(true);

        $mockBinProvider = $this->createMock(BinListProvider::class);
        $mockBinProvider->method('getCountryCode')->willReturn('FR');

        $mockExchangeProvider = $this->createMock(ExchangeRateProvider::class);
        $mockExchangeProvider->method('getRate')->willReturn(1.0);

        $rateService = new CommissionRateService($mockEuService);
        $calculator = new CommissionCalculator($mockBinProvider, $mockExchangeProvider, $rateService);

        $transaction = new Transaction(['bin' => '345678', 'amount' => 0.0, 'currency' => 'EUR']);
        $commission = $calculator->calculateCommission($transaction);

        $this->assertEquals('0.00', $commission);
    }

    public function testHandlesUnusualExchangeRates(): void
    {
        $mockEuService = $this->createMock(CountryService::class);
        $mockEuService->method('isEuCountry')->willReturn(true);

        $mockBinProvider = $this->createMock(BinListProvider::class);
        $mockBinProvider->method('getCountryCode')->willReturn('FR');

        $mockExchangeProvider = $this->createMock(ExchangeRateProvider::class);
        $mockExchangeProvider->method('getRate')->willReturn(0.5);

        $rateService = new CommissionRateService($mockEuService);
        $calculator = new CommissionCalculator($mockBinProvider, $mockExchangeProvider, $rateService);

        $transaction = new Transaction(['bin' => '456789', 'amount' => 200.0, 'currency' => 'Foreign']);
        $commission = $calculator->calculateCommission($transaction);

        $this->assertEquals('4.00', $commission);
    }
}
