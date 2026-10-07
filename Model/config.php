<?php

$host = 'iutinfo-sgbd.uphf.fr';
$port = '5432';
$dbname = 'iutinfo601';
$user = 'iutinfo601';
$password = 'q/eA/Sp6';

$pdo = new PDO(
    "pgsql:host=$host;port=$port;dbname=$dbname",
    $user,
    $password
);