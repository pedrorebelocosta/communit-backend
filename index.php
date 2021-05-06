<?php

use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();
$container->set('upload_directory', __DIR__ . '/uploads');

AppFactory::setContainer($container);

// Loading the environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = AppFactory::create();

// Add JWT Middleware to authenticate requests
$app->add(new Tuupola\Middleware\JwtAuthentication([
	"ignore" => ["/auth", "/signup"],
	"secure" => false,
	"secret" => $_ENV['SECRET_KEY'],
	"rules" => [
		new Tuupola\Middleware\JwtAuthentication\RequestPathRule([
			"path" => "/occurrence",
			"ignore" => []
		]),
		new Tuupola\Middleware\JwtAuthentication\RequestMethodRule([
			"ignore" => ["OPTIONS", "GET"]
		])
	]
]));

$app->addBodyParsingMiddleware();

$app->get('/', function (Request $request, Response $response, $args) {
	$response->getBody()->write("It works");
	return $response;
});

require_once('./controllers/AuthController.php');
require_once('./controllers/OccurrenceController.php');
require_once('./controllers/FileController.php');

$app->run();
