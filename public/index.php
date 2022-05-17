<?php
use Tuupola\Middleware\CorsMiddleware;

require '../vendor/autoload.php';

$app = new \Slim\App;
$app->pipe(CorsMiddleware::class);
//Rutas
require '../src/routes/mercado-pago.php';

$app->run();