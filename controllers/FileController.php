<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface as UploadedFile;

$app->post('/upload', function (Request $request, Response $response, $args) {
	$directory = $this->get('upload_directory');
	$uploadedFiles = $request->getUploadedFiles();
	$uploadedFile = $uploadedFiles['photo'];
	if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
		$filename = moveUploadedFile($directory, $uploadedFile);
		$result = json_encode(["photo_url" => "http://10.0.2.2:1337/uploads/".$filename]);
		$response->getBody()->write($result);
	}
	return $response->withStatus(201);
});

function moveUploadedFile(string $directory, UploadedFile $uploadedFile) {
	$extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
	$basename = bin2hex(random_bytes(8));
	$filename = sprintf('%s.%0.8s', $basename, $extension);
	$uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
	return $filename;
}
