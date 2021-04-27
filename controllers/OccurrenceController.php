<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/occurrence', function (Request $request, Response $response, $args) {
	require_once("db.php");
	$result = $db->occurrence()->select("id, user_id, description, ST_X(geom) as lat, ST_Y(geom) as lng, photo_url");
	$occurrences = iterator_to_array($result, false);
	$response->getBody()->write(json_encode($occurrences));
	return $response->withStatus(200);
});

$app->get('/occurrence/{id}', function (Request $request, Response $response, $args) {
	require_once("db.php");
	$foundOccurrence = $db->occurrence()->select("id, user_id, description, ST_X(geom) as lat, ST_Y(geom) as lng, photo_url")
										->where("id = ?", $args["id"])
										->limit(1)[$args["id"]];
	
	$response->getBody()->write(json_encode($foundOccurrence));
	return $foundOccurrence ? $response : $response->withStatus(404);
});

$app->post('/occurrence', function (Request $request, Response $response, $args) {
	require_once("db.php");
	$email = $request->getAttribute('token')["email"];
	$reqBody = $request->getParsedBody();
	if (!isset($reqBody)) {
		$resBody = json_encode([
			"status" => "The request body should contain all parameters neccessary to create a new occurrence",
		]);
		$response->getBody()->write($resBody);
		return $response;
	}
	$foundUser = $db->user()("email = ?", $email)->fetch();
	$newOccurrence = array(
		"id" => null,
		"user_id" => $foundUser["id"],
		"description" => $reqBody["description"],
		"geom" => new NotORM_Literal("Point(?,?)", $reqBody["lat"], $reqBody["lng"]),
		"photo_url" => $reqBody["photoUrl"]
	);
	$result = $db->occurrence()->insert($newOccurrence);
	return $result ? $response->withStatus(201) : $response->withStatus(400);
});

$app->put('/occurrence/{id}', function (Request $request, Response $response, $args) {
	require_once("db.php");
	$email = $request->getAttribute('token')["email"];
	$foundUser = $db->user()("email = ?", $email)->fetch();
	$reqBody = $request->getParsedBody();

	if (!$reqBody) { 
		$resBody = json_encode([
			"status" => "The request body should contain at least one parameter to update an occurrence",
		]);
		$response->getBody()->write($resBody);
		return $response;
	}

	$foundOccurrence = $db->occurrence()->select("id, user_id, description, ST_X(geom) as lat, ST_Y(geom) as lng, photo_url")
										->where("id = ?", $args["id"])
										->limit(1)[$args["id"]];

	if ($foundOccurrence["user_id"] !== $foundUser["id"]) return $response->withStatus(403);
	if (!$foundOccurrence) return $response->withStatus(404);

	$foundOccurrence["description"] = $reqBody["description"] ?? $foundOccurrence["description"];
	$foundOccurrence["photo_url"] = $reqBody["photo_url"] ?? $foundOccurrence["photo_url"];
	$foundOccurrence->update();
	return $response->withStatus(200);
});

$app->delete('/occurrence/{id}', function (Request $request, Response $response, $args) {
	require_once("db.php");
	$email = $request->getAttribute('token')["email"];
	$foundUser = $db->user()("email = ?", $email)->fetch();
	$foundOccurrence = $db->occurrence()->select("id, user_id, description, ST_X(geom) as lat, ST_Y(geom) as lng, photo_url")
										->where("id = ?", $args["id"])
										->limit(1)[$args["id"]];

	if ($foundOccurrence["user_id"] !== $foundUser["id"]) return $response->withStatus(403);
	if (!$foundOccurrence) return $response->withStatus(404);
	$foundOccurrence->delete();
	return $response->withStatus(200);
});
