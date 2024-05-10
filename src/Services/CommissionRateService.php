<?php

declare(strict_types=1);

namespace App\Services;

class CommissionRateService
{
    private CountryService $euCountryService;

    public function __construct(CountryService $euCountryService)
    {
        $this->euCountryService = $euCountryService;
    }

    public function getCommissionRate(string $countryCode): string
    {
        if ($this->euCountryService->isEuCountry($countryCode)) {
            return '0.01';
        }
        return '0.02';
    }
}
