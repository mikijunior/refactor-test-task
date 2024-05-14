<?php

declare(strict_types=1);

namespace App\Tests;

use App\DTO\Transaction;
use App\Providers\FileDataProvider;
use PHPUnit\Framework\TestCase;

class FileDataProviderTest extends TestCase
{
    private $tempFile;
    private FileDataProvider $dataProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempFile = tmpfile();
        $metaData = stream_get_meta_data($this->tempFile);
        $tempFileName = $metaData['uri'];

        $this->dataProvider = new FileDataProvider($tempFileName);
    }

    protected function tearDown(): void
    {
        fclose($this->tempFile);
    }

    public function testReadsEmptyFile(): void
    {
        $results = iterator_to_array($this->dataProvider->getData());
        $this->assertEmpty($results);
    }

    public function testHandlesMixedDataGracefully(): void
    {
        fwrite($this->tempFile, '{"bin":"123456","amount":"100.00","currency":"EUR"}' . "\n");
        fwrite($this->tempFile, '{"bin":"invalid","amount":"bad data","currency":"USD"}' . "\n");
        fwrite($this->tempFile, '{"bin":"234567","amount":"200.00","currency":"USD"}' . "\n");
        fseek($this->tempFile, 0);

        $results = iterator_to_array($this->dataProvider->getData());

        $this->assertCount(3, $results);
        $this->assertInstanceOf(Transaction::class, $results[0]);
        $this->assertEquals(
            new Transaction(['bin' => '123456', 'amount' => '100.00', 'currency' => 'EUR']),
            $results[0]
        );
        $this->assertNotNull($results[1]);
        $this->assertInstanceOf(Transaction::class, $results[2]);
        $this->assertEquals(
            new Transaction(['bin' => '234567', 'amount' => '200.00', 'currency' => 'USD']),
            $results[2]
        );
    }
}
