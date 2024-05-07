<?php

declare(strict_types=1);

namespace App\Tests;

use App\Exceptions\FileNotFoundException;
use App\Processors\TransactionProcessor;
use App\Providers\BinListProvider;
use App\Providers\ExchangeRateProvider;
use App\Providers\FileDataProvider;
use App\Services\CommissionCalculator;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class TransactionProcessorTest extends TestCase
{
    /**
     * @throws FileNotFoundException
     */
    public function testProcess()
    {
        $binMock = new MockHandler([
            new Response(200, [], '{"country": {"alpha2": "LT"}}'),
            new Response(200, [], '{"country": {"alpha2": "US"}}'),
        ]);

        $exchangeMock = new MockHandler([
            new Response(200, [], '{"rates": {"USD": 1.2}}'),
            new Response(200, [], '{"rates": {"EUR": 1, "USD": 1.2}}'),
        ]);

        $binProvider = new BinListProvider(new Client(['handler' => HandlerStack::create($binMock)]));
        $exchangeProvider = new ExchangeRateProvider(new Client(['handler' => HandlerStack::create($exchangeMock)]));
        $calculator = new CommissionCalculator($binProvider, $exchangeProvider);
        $processor = new TransactionProcessor($calculator);

        $filename = 'tests/test_data.txt';
        file_put_contents(
            $filename,
            '{"bin":"45717360","amount":"100.00","currency":"EUR"}' . PHP_EOL .
            '{"bin":"516793","amount":"50.00","currency":"USD"}' . PHP_EOL
        );
        $provider = new FileDataProvider($filename);

        $results = iterator_to_array($processor->process($provider));

        $this->assertCount(2, $results);
        $this->assertEquals(1.00, $results[0]);
        $this->assertEquals(0.84, $results[1]);

        unlink($filename);
    }
}
