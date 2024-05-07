<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\DataProviderInterface;
use App\Exceptions\FileNotFoundException;
use JsonException;
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

    public function getData(): \Iterator
    {
        if (($file = fopen($this->filename, 'r')) === false) {
            throw new RuntimeException("Unable to open file: {$this->filename}");
        }

        while (($line = fgets($file)) !== false) {
            try {
                yield json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new RuntimeException("Invalid JSON format in file: {$this->filename}");
            }
        }

        fclose($file);
    }
}
