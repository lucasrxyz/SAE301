<?php
// Adresse et informations de connexion à la base Supabase
$hoteDirect = 'db.juleatlpngxjdveccilz.supabase.co';
$port = '5432';
$dbname = 'postgres';
$password = 'Aqwzsxed59330';

// Teste si l'adresse directe répond (elle ne marche qu'en IPv6)
$testIpv6 = @fsockopen($hoteDirect, 5432, $codeErreur, $messageErreur, 1);

// Si elle répond on l'utilise, sinon on passe par le pooler de Supabase
if ($testIpv6) {
    fclose($testIpv6);
    $host = $hoteDirect;
    $user = 'postgres';
} else {
    $host = 'aws-1-eu-west-3.pooler.supabase.com';
    $user = 'postgres.juleatlpngxjdveccilz';
}

// Ouvre la connexion : $pdo sert ensuite à faire toutes les requêtes
$pdo = new PDO(
    "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
    $user,
    $password,
    [
        PDO::ATTR_PERSISTENT => true,
        // Options : garde la connexion, affiche les erreurs SQL, et renvoie les lignes avec le nom des colonnes
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

// Affiche une date en français, avec l'heure seulement si elle n'est pas minuit
function formaterDate($valeur)
{
    $horodatage = strtotime($valeur);
    return date('H:i', $horodatage) === '00:00' ? date('d/m/Y', $horodatage) : date('d/m/Y H:i', $horodatage);
}
