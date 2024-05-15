<?php

declare(strict_types=1);

namespace App\Tests;

use App\Contracts\BinProviderInterface;
use App\Services\CommissionRate;
use App\Services\EuCountriesSpecification;
use PHPUnit\Framework\TestCase;

class CommissionRateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockBinProvider = $this->createMock(BinProviderInterface::class);
        $this->service = new CommissionRate($this->mockBinProvider, '0.02');

        $this->service->addSpecification(new EuCountriesSpecification());
    }

    public function testCommissionRateForEuCountry(): void
    {
        $this->mockBinProvider->method('getCountryCode')->willReturn('AT');

        $this->service->addSpecification(new EuCountriesSpecification());

        $rate = $this->service->getCommissionRate('123456');
        $this->assertEquals('0.01', $rate);
    }

    public function testCommissionRateForNonEuCountry(): void
    {
        $this->mockBinProvider->method('getCountryCode')->willReturn('US');

        $rate = $this->service->getCommissionRate('654321');
        $this->assertEquals('0.02', $rate);
    }

    public function testUsesDefaultRateWhenNoSpecificationMatches(): void
    {
        $this->mockBinProvider->method('getCountryCode')->willReturn('CA');

        $this->service->addSpecification(new EuCountriesSpecification());

        $rate = $this->service->getCommissionRate('987654');
        $this->assertEquals('0.02', $rate);
    }

    public function testUsesDefaultRateWhenNoSpecificationAdded(): void
    {
        $this->mockBinProvider->method('getCountryCode')->willReturn('CA');

        $rate = $this->service->getCommissionRate('987654');
        $this->assertEquals('0.02', $rate);
    }
}
