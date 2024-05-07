<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use App\Exceptions\FileNotFoundException;
use App\Processors\TransactionProcessor;
use App\Providers\BinListProvider;
use App\Providers\ExchangeRateProvider;
use App\Providers\FileDataProvider;
use App\Services\CommissionCalculator;
use GuzzleHttp\Client;

if ($argc != 2) {
    echo "Usage: php app.php <input_file>" . PHP_EOL;
    exit(1);
}

$inputFile = $argv[1];

try {
    $dataProvider = new FileDataProvider($inputFile);
    $client = new Client();
    $calculator = new CommissionCalculator(new BinListProvider($client), new ExchangeRateProvider($client));
    $processor = new TransactionProcessor($calculator);

    foreach ($processor->process($dataProvider) as $result) {
        echo $result . PHP_EOL;
    }

} catch (FileNotFoundException $e) {
    echo "File Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Exception $e) {
    echo "General Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
