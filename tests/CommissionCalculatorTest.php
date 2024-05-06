<?php

declare(strict_types=1);

namespace App\Tests;

use App\CommissionCalculator;
use App\DTO\Transaction;
use App\Exceptions\BinDataRetrievalException;
use App\Exceptions\InvalidTransactionFormatException;
use App\Providers\BinListProvider;
use App\Providers\ExchangeRateProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    public function testCalculateCommissionForEu()
    {
        $binMock = new MockHandler([
            new Response(200, [], '{"country": {"alpha2": "LT"}}'),
        ]);
        $exchangeMock = new MockHandler([
            new Response(200, [], '{"rates": {"USD": 1.2}}'),
        ]);

        $binProvider = new BinListProvider(new Client(['handler' => HandlerStack::create($binMock)]));
        $exchangeProvider = new ExchangeRateProvider(new Client(['handler' => HandlerStack::create($exchangeMock)]));

        $calculator = new CommissionCalculator($binProvider, $exchangeProvider);
        $transaction = new Transaction(['bin' => '45717360', 'amount' => 120, 'currency' => 'USD']);
        $result = $calculator->calculateCommission($transaction);

        $this->assertEquals(1.00, $result);
    }

    public function testCalculateCommissionForNonEu()
    {
        $binMock = new MockHandler([
            new Response(200, [], '{"country": {"alpha2": "US"}}'),
        ]);
        $exchangeMock = new MockHandler([
            new Response(200, [], '{"rates": {"USD": 1.2}}'),
        ]);

        $binProvider = new BinListProvider(new Client(['handler' => HandlerStack::create($binMock)]));
        $exchangeProvider = new ExchangeRateProvider(new Client(['handler' => HandlerStack::create($exchangeMock)]));

        $calculator = new CommissionCalculator($binProvider, $exchangeProvider);
        $transaction = new Transaction(['bin' => '516793', 'amount' => 50, 'currency' => 'USD']);
        $result = $calculator->calculateCommission($transaction);

        $this->assertEquals(0.84, $result);
    }

    public function testCalculateCommissionZeroAmount()
    {
        $binMock = new MockHandler([
            new Response(200, [], '{"country": {"alpha2": "LT"}}'),
        ]);
        $exchangeMock = new MockHandler([
            new Response(200, [], '{"rates": {"USD": 1.2}}'),
        ]);

        $binProvider = new BinListProvider(new Client(['handler' => HandlerStack::create($binMock)]));
        $exchangeProvider = new ExchangeRateProvider(new Client(['handler' => HandlerStack::create($exchangeMock)]));

        $calculator = new CommissionCalculator($binProvider, $exchangeProvider);
        $transaction = new Transaction(['bin' => '45717360', 'amount' => 0, 'currency' => 'USD']);
        $result = $calculator->calculateCommission($transaction);

        $this->assertEquals(0.00, $result);
    }

    public function testInvalidJson()
    {
        $binProvider = $this->createMock(BinListProvider::class);
        $exchangeProvider = $this->createMock(ExchangeRateProvider::class);

        $calculator = new CommissionCalculator($binProvider, $exchangeProvider);

        $this->expectException(JsonException::class);
        new Transaction(json_decode("{invalid_json}", true, 512, JSON_THROW_ON_ERROR));
    }

    public function testMissingFields()
    {
        $binProvider = $this->createMock(BinListProvider::class);
        $exchangeProvider = $this->createMock(ExchangeRateProvider::class);

        $calculator = new CommissionCalculator($binProvider, $exchangeProvider);

        $this->expectException(InvalidTransactionFormatException::class);
        $transaction = new Transaction(['bin' => '45717360']);
    }

    public function testInvalidBin()
    {
        $binMock = new MockHandler([
            new Response(404, []),
        ]);
        $exchangeMock = new MockHandler([
            new Response(200, [], '{"rates": {"USD": 1.2}}'),
        ]);

        $binProvider = new BinListProvider(new Client(['handler' => HandlerStack::create($binMock)]));
        $exchangeProvider = new ExchangeRateProvider(new Client(['handler' => HandlerStack::create($exchangeMock)]));

        $calculator = new CommissionCalculator($binProvider, $exchangeProvider);
        $transaction = new Transaction(['bin' => '00000000', 'amount' => 100, 'currency' => 'USD']);

        $this->expectException(BinDataRetrievalException::class);
        $calculator->calculateCommission($transaction);
    }

    /**
     * @throws InvalidTransactionFormatException
     */
    public function testNetworkIssues()
    {
        $binMock = new MockHandler([
            new ConnectException("Connection error", new Request("GET", "test")),
        ]);
        $exchangeMock = new MockHandler([
            new ConnectException("Connection error", new Request("GET", "test")),
        ]);

        $binProvider = new BinListProvider(new Client(['handler' => HandlerStack::create($binMock)]));
        $exchangeProvider = new ExchangeRateProvider(new Client(['handler' => HandlerStack::create($exchangeMock)]));

        $calculator = new CommissionCalculator($binProvider, $exchangeProvider);
        $transaction = new Transaction(['bin' => '45717360', 'amount' => 100, 'currency' => 'USD']);

        $this->expectException(BinDataRetrievalException::class);
        $calculator->calculateCommission($transaction);
    }
}
