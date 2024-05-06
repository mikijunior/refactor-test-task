<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use App\CommissionCalculator;
use App\DTO\Transaction;
use App\Exceptions\BinDataRetrievalException;
use App\Exceptions\ExchangeRateRetrievalException;
use App\Exceptions\FileNotFoundException;
use App\Exceptions\InvalidTransactionFormatException;
use App\Providers\BinListProvider;
use App\Providers\ExchangeRateProvider;
use GuzzleHttp\Client;

if ($argc != 2) {
    throw FileNotFoundException::fileNotFound($argv[1]);
}

$inputFile = $argv[1];
if (!file_exists($inputFile)) {
    throw FileNotFoundException::fileNotFound($inputFile);
}

$client = new Client();
$calculator = new CommissionCalculator(new BinListProvider($client), new ExchangeRateProvider($client));

foreach (explode("\n", file_get_contents($inputFile)) as $row) {
    if (empty($row)) {
        continue;
    }

    try {
        $transaction = new Transaction(json_decode($row, true, 512, JSON_THROW_ON_ERROR));
        $commission = $calculator->calculateCommission($transaction);
        echo $commission . PHP_EOL;

    } catch (JsonException $e) {
        echo InvalidTransactionFormatException::invalidJson()->getMessage() . PHP_EOL;
    } catch (InvalidTransactionFormatException | BinDataRetrievalException | ExchangeRateRetrievalException | FileNotFoundException $e) {
        echo $e->getMessage() . PHP_EOL;
    } catch (Exception $e) {
        echo "General Error: " . $e->getMessage() . PHP_EOL;
    }
}
