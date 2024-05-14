<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Exceptions\FileNotFoundException;
use App\Processors\TransactionProcessor;
use App\Providers\FileDataProvider;
use App\Services\CommissionCalculator;

$container = include __DIR__ . '/bootstrap.php';

if ($argc != 2) {
    echo "Usage: php app.php <input_file>" . PHP_EOL;
    exit(1);
}

$inputFile = $argv[1];

try {
    $dataProvider = new FileDataProvider($inputFile);

    $processor = new TransactionProcessor(
        $container->get(CommissionCalculator::class),
        $dataProvider
    );

    $processor->run();

} catch (FileNotFoundException $e) {
    echo "File Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
