<?php

namespace App\Output;

use App\Contracts\CommissionFormatterInterface;

class CommissionFormatter implements CommissionFormatterInterface
{
    private int $precision;

    public function __construct(int $precision)
    {
        $this->precision = $precision;
    }

    public function prettify(string $commission): string
    {
        $pow = pow(10, $this->precision);
        $result = ceil((float)$commission * $pow) / $pow;
        $format = "%." . $this->precision . "f";

        return sprintf($format, $result);
    }
}
