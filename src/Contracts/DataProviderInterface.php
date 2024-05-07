<?php

declare(strict_types=1);

namespace App\Contracts;

use Iterator;

interface DataProviderInterface
{
    /**
     * Get the data for processing.
     *
     * @return Iterator
     */
    public function getData(): Iterator;
}
