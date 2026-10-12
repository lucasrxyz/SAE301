<?php
$host = 'aws-0-eu-west-1.pooler.supabase.com';
$port = '5432';
$dbname = 'postgres';
$user = 'postgres.mhppkfbxenttmqfutfay';
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