<?php

declare(strict_types=1);

namespace App\Services;

class CountryService
{
    public const EU_COUNTRIES = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI',
        'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT',
        'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK',
    ];

    public function isEuCountry(string $countryCode): bool
    {
        return in_array($countryCode, self::EU_COUNTRIES, true);
    }
}
