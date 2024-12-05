<?php

namespace app\Config;

class DataLoader
{
    private array $data;
    private const FILE_PATH = 'data.json';

    public function __construct()
    {
        $this->reloadData();
    }

    public function reloadData(): void
    {
        $data = json_decode(file_get_contents($filePath ?? self::FILE_PATH), true);
        $data = !is_array($data) ? [] : $data;
        $this->data = $data;
    }

    public function findAll(): array
    {
        return $this->data;
    }

    public function saveData(mixed $data, string $type): void
    {
        $this->reloadData();
        $this->data[$type] = array_values($data);
        file_put_contents(self::FILE_PATH, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}
