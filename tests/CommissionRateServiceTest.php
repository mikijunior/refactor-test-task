<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Services\CommissionRateService;
use App\Services\EuCountriesSpecification;
use App\Contracts\BinProviderInterface;
use App\DTO\Transaction;

class CommissionRateServiceTest extends TestCase
{
    public function testCommissionRateForEuCountry(): void
    {
        $mockBinProvider = $this->createMock(BinProviderInterface::class);
        $mockBinProvider->method('getCountryCode')->willReturn('AT');

        $service = new CommissionRateService($mockBinProvider, '0.02');
        $service->addSpecification(new EuCountriesSpecification());

        $rate = $service->getCommissionRate('123456');
        $this->assertEquals('0.01', $rate);
    }

    public function testCommissionRateForNonEuCountry(): void
    {
        $mockBinProvider = $this->createMock(BinProviderInterface::class);
        $mockBinProvider->method('getCountryCode')->willReturn('US');

        $service = new CommissionRateService($mockBinProvider, '0.02');

        $rate = $service->getCommissionRate('654321');
        $this->assertEquals('0.02', $rate);
    }

    public function testUsesDefaultRateWhenNoSpecificationMatches(): void
    {
        $mockBinProvider = $this->createMock(BinProviderInterface::class);
        $mockBinProvider->method('getCountryCode')->willReturn('CA');

        $service = new CommissionRateService($mockBinProvider, '0.02');
        $service->addSpecification(new EuCountriesSpecification());

        $rate = $service->getCommissionRate('987654');
        $this->assertEquals('0.02', $rate);
    }

    public function testUsesDefaultRateWhenNoSpecificationAdded(): void
    {
        $mockBinProvider = $this->createMock(BinProviderInterface::class);
        $mockBinProvider->method('getCountryCode')->willReturn('CA');

        $service = new CommissionRateService($mockBinProvider, '0.02');

        $rate = $service->getCommissionRate('987654');
        $this->assertEquals('0.02', $rate);
    }
}
