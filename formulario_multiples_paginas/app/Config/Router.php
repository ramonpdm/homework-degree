<?php

namespace app\Config;

use App\Controllers\Controller;
use Exception;

class Router
{
    /** @var Controller[] */
    private array $routes = [];

    public function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Allow: GET, POST, OPTIONS, PUT, DELETE");
        header('Content-Type: application/json');
    }

    public function addRoute(string $route, Controller $controller): void
    {
        $this->routes[$route] = $controller;
    }

    public function handleRequest(): string
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                return $this->handleOptionsRequest();
            }

            $route = $_GET['ruta'] ?? null;

            if (isset($this->routes[$route])) {
                return $this->routes[$route]->handle();
            } else {
                return Controller::sendOutput(['error' => 'Ruta no encontrada'], 404);
            }
        } catch (Exception $e) {
            return Controller::sendOutput(['error' => $e->getMessage()], 500);
        }
    }

    private function handleOptionsRequest(): string
    {
        header('Content-Length: 0');
        header('Content-Type: text/plain');
        http_response_code(200);
        return '';
    }
}
