<?php

namespace App\Contracts;

interface SpecificationInterface
{
    public function supports(string $countryCode): bool;
    public function getCoefficient(): string;
}
