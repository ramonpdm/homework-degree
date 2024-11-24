<?php

namespace App\Controllers;

use app\Config\DataLoader;

abstract class Controller
{
    protected DataLoader $dataLoader;

    public function __construct(DataLoader $dataLoader)
    {
        $this->dataLoader = $dataLoader;
    }

    abstract public function handle(): string;

    protected function getPayload(): array
    {
        $input = file_get_contents('php://input');
        return (array)json_decode($input, true) ?? [];
    }

    public static function sendOutput($data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        return json_encode($data);
    }
}
