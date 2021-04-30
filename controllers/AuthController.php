<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;

$app->post('/auth', function (Request $request, Response $response, $args) {
	require_once("db.php");
	$body = $request->getParsedBody();
	if (!isset($body)) return $response->withStatus(400);
	if (!(isset($body['email']) && isset($body['password']))) {
		return $response->withStatus(400);
	}
	$foundUser = $db->user()("email = ?", $body['email'])->fetch();
	if (!password_verify($body['password'], $foundUser['password'])) {
		$responseBody = json_encode([
			"authenticated" => false,
			"status" => "Username and/or password incorrect",
		]);
		$response->getBody()->write($responseBody);
		return $response;
	}

	/*
		After all verifications, everything seems to be fine
		Returning a JWT to client
	*/
	$payload = array(
		"iss" => "localhost:1337",
		"aud" => "com.cpe.communit:app",
		"iat" => time(),
		"exp" => strtotime("+1 year"),
		"email" => $foundUser["email"],
		"first_name" => $foundUser["firstname"],
		"last_name" => $foundUser["lastname"]
	);
	$responseBody = json_encode([
		"authenticated" => true,
		"auth_token" => JWT::encode($payload, $_ENV['SECRET_KEY'])
	]);
	$response->getBody()->write($responseBody);
	return $response;
});

$app->post('/signup', function (Request $request, Response $response, $args) {
	require_once("db.php");
	$body = $request->getParsedBody();
	if (!isset($body)) return $response->withStatus(400);
	if (!(isset($body['email']) && isset($body['password']))) {
		return $response->withStatus(400);
	}
	//	$foundUser = $db->user()("email = ?", $body['email'])->fetch();
	$body["password"] = password_hash($body["password"], PASSWORD_DEFAULT);
	$result = $db->user()->insert($body);
	return $result ? $response->withStatus(201) : $response->withStatus(400);
});