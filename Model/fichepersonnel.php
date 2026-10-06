<?php
require_once __DIR__ . '/config.php';

$nomUtilisateur = $_GET['nom'] ?? '';
$voirTout = isset($_GET['tout']);

$resultats = [];
$aucunResultat = false;

if ($voirTout) {
    $requete = $pdo->prepare("SELECT * FROM Personnel WHERE id IN (SELECT id_personnel FROM Rattachement) ORDER BY nom, prenom");
    $requete->execute();
    $resultats = $requete->fetchAll(PDO::FETCH_ASSOC);

} elseif ($nomUtilisateur !== '') {
    $requete = $pdo->prepare("SELECT * FROM Personnel WHERE nom ILIKE :nom AND id IN (SELECT id_personnel FROM Rattachement) ORDER BY nom, prenom");
    $requete->execute(['nom' => '%' . $nomUtilisateur . '%']);
    $resultats = $requete->fetchAll(PDO::FETCH_ASSOC);

    if (count($resultats) === 0) {
        $aucunResultat = true;
    }
}