<?php

namespace App\Exceptions;

use Exception;

class FileNotFoundException extends Exception
{
    public static function fileNotFound($filename): FileNotFoundException
    {
        return new self("File not found: $filename");
    }
}
