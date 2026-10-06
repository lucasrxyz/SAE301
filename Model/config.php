<?php

$host = '127.0.0.1';
$port = '5432';
$dbname = 'gscientifique';
$user = 'postgres';
$password = '123';

$pdo = new PDO(
    "pgsql:host=$host;port=$port;dbname=$dbname",
    $user,
    $password
);