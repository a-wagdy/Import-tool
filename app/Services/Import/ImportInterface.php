<?php

declare(strict_types=1);

namespace App\Services\Import;

interface ImportInterface
{
    public function processFile(string $filePath): void;
    public function getFileMappedData(): array;
}
