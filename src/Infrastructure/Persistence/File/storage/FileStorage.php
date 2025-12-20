<?php

namespace App\Infrastructure\Storage;
use RuntimeException;

class FileStorage
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([]));
        }
    }

    public function read(): array
    {
        $content = file_get_contents($this->filePath);
        if ($content === false) {
            throw new RuntimeException("cannot read storage");
        }
        return json_decode($content, true) ?? [];
    }

    public function write(array $data): void
    {
        file_put_contents(
            $this->filePath,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
