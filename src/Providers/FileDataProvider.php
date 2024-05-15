<?php

namespace App\Providers;

use App\Contracts\DataProviderInterface;
use App\DTO\Transaction;
use App\Exceptions\FileNotFoundException;
use Generator;
use RuntimeException;

class FileDataProvider implements DataProviderInterface
{
    private string $filename;

    /**
     * @throws FileNotFoundException
     */
    public function __construct(string $filename)
    {
        if (!file_exists($filename)) {
            throw FileNotFoundException::fileNotFound($filename);
        }

        $this->filename = $filename;
    }

    public function getData(): Generator
    {
        if (!file_exists($this->filename) || !is_readable($this->filename)) {
            throw new RuntimeException("Cannot open file: {$this->filename}");
        }

        $handle = fopen($this->filename, 'rb');

        if ($handle === false) {
            throw new RuntimeException("Failed to open file: {$this->filename}");
        }

        try {
            while (($line = fgets($handle)) !== false) {
                $data = json_decode($line, true);
                if ($this->isValidTransactionData($data)) {
                    yield new Transaction($data['bin'], $data['amount'], $data['currency']);
                }
            }
        } finally {
            fclose($handle);
        }
    }

    private function isValidTransactionData($data): bool
    {
        return is_array($data) &&
            isset($data['bin'], $data['amount'], $data['currency']) &&
            is_string($data['bin']) &&
            is_string($data['amount']) &&
            is_string($data['currency']);
    }
}
