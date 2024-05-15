<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class Math
{
    private int $scale;

    public function __construct(int $scale)
    {
        $this->scale = $scale;
    }

    public function add(string $a, string $b): string
    {
        return bcadd($a, $b, $this->scale);
    }

    public function subtract(string $a, string $b): string
    {
        return bcsub($a, $b, $this->scale);
    }

    public function multiply(string $a, string $b): string
    {
        return bcmul($a, $b, $this->scale);
    }

    public function divide(string $a, string $b): string
    {
        if (bccomp($b, '0', $this->scale) === 0) {
            throw new RuntimeException("Division by zero");
        }
        return bcdiv($a, $b, $this->scale);
    }

    public function compare(string $a, string $b): int
    {
        return bccomp($a, $b, $this->scale);
    }
}
