<?php
require_once __DIR__ . '/config.php';

$idPersonnel = $_GET['id'] ?? null;
$personnel = null;
$personnelIntrouvable = false;

if ($idPersonnel !== null) {
    $requete = $pdo->prepare("SELECT * FROM Personnel WHERE id = :id");
    $requete->execute(['id' => $idPersonnel]);
    $personnel = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$personnel) {
        $personnelIntrouvable = true;
    }
}