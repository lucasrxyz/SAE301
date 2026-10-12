<?php
$host = 'aws-1-eu-west-3.pooler.supabase.com';
$port = '5432';
$dbname = 'postgres';
$user = 'postgres.juleatlpngxjdveccilz';
$password = 'Aqwzsxed59330';

$pdo = new PDO(
    "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
    $user,
    $password,
    [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);