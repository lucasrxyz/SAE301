<?php
$hoteDirect = 'db.juleatlpngxjdveccilz.supabase.co';
$port = '5432';
$dbname = 'postgres';
$password = 'Aqwzsxed59330';

$testIpv6 = @fsockopen($hoteDirect, 5432, $codeErreur, $messageErreur, 1);

if ($testIpv6) {
    fclose($testIpv6);
    $host = $hoteDirect;
    $user = 'postgres';
} else {
    $host = 'aws-1-eu-west-3.pooler.supabase.com';
    $user = 'postgres.juleatlpngxjdveccilz';
}

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