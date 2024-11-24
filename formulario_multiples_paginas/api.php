<?php

require 'app/Config/Router.php';
require 'app/Config/DataLoader.php';
require 'app/Controllers/Controller.php';
require 'app/Controllers/PersonController.php';
require 'app/Controllers/EnfermedadController.php';
require 'app/Controllers/InternamientoController.php';

use app\Config\DataLoader;
use app\Config\Router;

use App\Controllers\EnfermedadController;
use App\Controllers\PersonController;
use App\Controllers\InternamientoController;

$dataLoader = new DataLoader();
$router = new Router();

$router->addRoute('personas', new PersonController($dataLoader));
$router->addRoute('enfermedades', new EnfermedadController($dataLoader));
$router->addRoute('internamientos', new InternamientoController($dataLoader));

echo $router->handleRequest();
