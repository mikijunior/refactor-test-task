<?php

namespace App\Contracts;

use Generator;
use App\DTO\Transaction;

interface DataProviderInterface
{
    /**
     * @return Generator|Transaction[]
     */
    public function getData(): Generator;
}
