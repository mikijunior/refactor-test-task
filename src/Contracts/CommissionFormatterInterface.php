<?php

namespace App\Contracts;

interface CommissionFormatterInterface
{
    public function prettify(string $commission): string;
}
