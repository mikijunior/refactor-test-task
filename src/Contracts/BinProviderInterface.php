<?php

declare(strict_types=1);

namespace App\Contracts;

interface BinProviderInterface
{
    public function getCountryCode(string $bin): string;
}
