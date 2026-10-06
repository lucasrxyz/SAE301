<?php
require_once __DIR__ . '/config.php';

$nomUtilisateur = $_GET['nom'] ?? '';
$voirTout = isset($_GET['tout']);
$indexAffiche = $_GET['voir'] ?? null;

$resultats = [];
$aucunResultat = false;
$ligneChoisie = null;
$parametreRetour = '';

if ($voirTout) {
    $requete = $pdo->prepare("SELECT * FROM Personnel ORDER BY nom, prenom");
    $requete->execute();
    $resultats = $requete->fetchAll(PDO::FETCH_ASSOC);
    $parametreRetour = 'tout=1';

} elseif ($nomUtilisateur !== '') {
    $requete = $pdo->prepare("SELECT * FROM Personnel WHERE nom ILIKE :nom ORDER BY nom, prenom");
    $requete->execute(['nom' => '%' . $nomUtilisateur . '%']);
    $resultats = $requete->fetchAll(PDO::FETCH_ASSOC);

    if (count($resultats) === 0) {
        $aucunResultat = true;
    }
    $parametreRetour = 'nom=' . urlencode($nomUtilisateur);
}

if ($indexAffiche !== null && isset($resultats[$indexAffiche])) {
    $ligneChoisie = $resultats[$indexAffiche];
}