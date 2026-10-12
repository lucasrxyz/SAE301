<?php
require_once __DIR__ . '/config.php';

$nomUtilisateur = trim($_GET['nom'] ?? '');
$voirTout = isset($_GET['tout']);
$voirRattaches = isset($_GET['rattaches']);

$resultats = [];
$aucunResultat = false;

$perimetreAutorise = perimetreUtilisateur($pdo);
[$filtrePerimetre, $parametres] = conditionPerimetre($perimetreAutorise);

$texteRequete = "SELECT DISTINCT Personnel.*, Rattachement.id IS NULL AS sans_rattachement
    FROM Personnel
    LEFT JOIN Rattachement ON Rattachement.id_personnel = Personnel.id
    LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
    LEFT JOIN Personnel_Nom_Historique ON Personnel_Nom_Historique.id_personnel = Personnel.id
    WHERE TRUE" . $filtrePerimetre;

if ($voirTout) {
    $requete = $pdo->prepare($texteRequete . " ORDER BY Personnel.nom, Personnel.prenom");
    $requete->execute($parametres);
    $resultats = $requete->fetchAll(PDO::FETCH_ASSOC);
} elseif ($voirRattaches) {
    $requete = $pdo->prepare($texteRequete . " AND Rattachement.id IS NOT NULL ORDER BY Personnel.nom, Personnel.prenom");
    $requete->execute($parametres);
    $resultats = $requete->fetchAll(PDO::FETCH_ASSOC);
} elseif ($nomUtilisateur !== '') {
    $requete = $pdo->prepare($texteRequete . "
        AND (Personnel.nom ILIKE :nom
        OR Personnel_Nom_Historique.ancien_nom ILIKE :nom
        OR Personnel.id_orcid = :exact
        OR Personnel.id_idhal = :exact)
        ORDER BY Personnel.nom, Personnel.prenom");
    $parametres['nom'] = '%' . $nomUtilisateur . '%';
    $parametres['exact'] = $nomUtilisateur;
    $requete->execute($parametres);
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