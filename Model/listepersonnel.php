<?php
require_once __DIR__ . '/config.php';

$nomUtilisateur = trim($_GET['nom'] ?? '');
$voirTout = isset($_GET['tout']);

$resultats = [];
$aucunResultat = false;

$texteRequete = "SELECT DISTINCT Personnel.*
    FROM Personnel
    JOIN Rattachement ON Rattachement.id_personnel = Personnel.id
    LEFT JOIN Personnel_Nom_Historique ON Personnel_Nom_Historique.id_personnel = Personnel.id";

if ($voirTout) {
    $requete = $pdo->prepare($texteRequete . " ORDER BY Personnel.nom, Personnel.prenom");
    $requete->execute();
    $resultats = $requete->fetchAll(PDO::FETCH_ASSOC);
} elseif ($nomUtilisateur !== '') {
    $requete = $pdo->prepare($texteRequete . "
        WHERE (Personnel.nom ILIKE :nom
        OR Personnel_Nom_Historique.ancien_nom ILIKE :nom
        OR Personnel.id_orcid = :exact
        OR Personnel.id_idhal = :exact)
        ORDER BY Personnel.nom, Personnel.prenom");
    $requete->execute(['nom' => '%' . $nomUtilisateur . '%', 'exact' => $nomUtilisateur]);
    $resultats = $requete->fetchAll(PDO::FETCH_ASSOC);

    if (count($resultats) === 0) {
        $aucunResultat = true;
    }
}

$requete = $pdo->query("SELECT nom, prenom FROM Personnel GROUP BY nom, prenom HAVING COUNT(*) > 1");
$homonymes = [];
foreach ($requete->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
    $homonymes[] = $ligne['nom'] . ' ' . $ligne['prenom'];
}