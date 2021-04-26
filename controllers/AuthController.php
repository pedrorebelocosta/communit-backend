<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;

$app->post('/auth', function (Request $request, Response $response, $args) {
    require_once("db.php");
    $body = $request->getParsedBody();
    if (isset($body)) {
        if (!(isset($body['email']) && isset($body['password']))) {
            return;
        }
        $foundUser = $db->user()("email = ?", $body['email'])->fetch();
        if (password_verify($body['password'], $foundUser['password'])) {
            // Returning a JWT to client
            $payload = array(
                "iss" => "localhost:1337",
                "aud" => "com.cpe.communit:app",
                "iat" => time(),
                "exp" => strtotime("+1 year"),
                "email" => $foundUser['email']
            );
            $responseBody = json_encode([
                "authenticated" => true,
                "auth_token"=> JWT::encode($payload, $_ENV['SECRET_KEY'])
            ]);
            $response->getBody()->write($responseBody);
            return $response;
        } else {
            $responseBody = json_encode([
                "authenticated" => false,
                "status"=> "Username and/or password incorrect",
            ]);
            $response->getBody()->write($responseBody);
            return $response;
        }
    }
});

?>