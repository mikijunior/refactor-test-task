<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\BinProviderInterface;
use App\Contracts\SpecificationInterface;

class CommissionRateService
{
    private BinProviderInterface $binProvider;
    private string $defaultCoefficient;
    /**
     * @var SpecificationInterface[]
     */
    private array $specifications = [];

    public function __construct(BinProviderInterface $binProvider, string $defaultCommissionRate = '0.02')
    {
        $this->binProvider = $binProvider;
        $this->defaultCoefficient = $defaultCommissionRate;
        $this->addSpecification(new EuCountriesSpecification());
    }

    public function addSpecification(SpecificationInterface $specification): void
    {
        $this->specifications[] = $specification;
    }

    public function getCommissionRate(string $bin): string
    {
        $countryCode = $this->binProvider->getCountryCode($bin);

        foreach ($this->specifications as $specification) {
            if ($specification->supports($countryCode)) {
                return $specification->getCoefficient();
            }
        }

        return $this->defaultCoefficient;
    }
}
