<?php
$pdo = new PDO($_ENV['DB_DSN'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
$db = new NotORM($pdo);
?>