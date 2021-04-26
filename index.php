<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
// use Firebase\JWT\JWT;

require __DIR__ . '/vendor/autoload.php';

// Loading the environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response, $args) {
    require_once("db.php");
    $users = $db->user()->select("*");
    $response->getBody()->write(json_encode($users));
    return $response;
});

$app->run();