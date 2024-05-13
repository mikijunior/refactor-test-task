<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Providers\FileDataProvider;
use App\DTO\Transaction;

class FileDataProviderTest extends TestCase
{
    public function testReadsEmptyFile(): void
    {
        $tempFile = tmpfile();
        $metaData = stream_get_meta_data($tempFile);
        $tempFileName = $metaData['uri'];

        $dataProvider = new FileDataProvider($tempFileName);

        $results = iterator_to_array($dataProvider->getData());

        $this->assertEmpty($results);

        fclose($tempFile);
    }

    public function testHandlesMixedDataGracefully(): void
    {
        $tempFile = tmpfile();
        fwrite($tempFile, '{"bin":"123456","amount":"100.00","currency":"EUR"}' . "\n");
        fwrite($tempFile, '{"bin":"invalid","amount":"bad data","currency":"USD"}' . "\n");
        fwrite($tempFile, '{"bin":"234567","amount":"200.00","currency":"USD"}' . "\n");
        fseek($tempFile, 0);

        $metaData = stream_get_meta_data($tempFile);
        $tempFileName = $metaData['uri'];

        $dataProvider = new FileDataProvider($tempFileName);
        $results = iterator_to_array($dataProvider->getData());

        $this->assertCount(3, $results);
        $this->assertInstanceOf(Transaction::class, $results[0]);
        $this->assertEquals(
            new Transaction([
                'bin' => '123456',
                'amount' => '100.00',
                'currency' => 'EUR',
            ]),
            $results[0]
        );
        $this->assertNotNull($results[1]);
        $this->assertInstanceOf(Transaction::class, $results[2]);
        $this->assertEquals(
            new Transaction([
                'bin' => '234567',
                'amount' => '200.00',
                'currency' => 'USD',
            ]),
            $results[2]
        );

        fclose($tempFile);
    }
}
