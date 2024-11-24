<?php

require 'app/Config/Router.php';
require 'app/Config/DataLoader.php';
require 'app/Controllers/Controller.php';
require 'app/Controllers/PersonController.php';

use app\Config\DataLoader;
use app\Config\Router;

use App\Controllers\Controller;
use App\Controllers\PersonController;

try {
    $dataLoader = new DataLoader();

    $router = new Router();
    $router->addRoute('personas', new PersonController($dataLoader));
    echo $router->handleRequest();
} catch (Throwable $e) {
    echo Controller::sendOutput(['error' => $e->getMessage()], 500);
    exit;
}
