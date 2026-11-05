<?php
// Connexion à la base de données
require_once __DIR__ . '/config.php';

// Ce que l'utilisateur a demandé : un nom, tout le monde, ou seulement les rattachés
$nomUtilisateur = trim($_GET['nom'] ?? '');
$voirTout = isset($_GET['tout']);
$voirRattaches = isset($_GET['rattaches']);

// Résultats vides au départ
$resultats = [];
$aucunResultat = false;

// Limite la recherche à ce que la personne a le droit de voir
$perimetreAutorise = perimetreUtilisateur($pdo);
[$filtrePerimetre, $parametres] = conditionPerimetre($perimetreAutorise);

// Début de la requête, commun aux trois recherches
$texteRequete = "SELECT DISTINCT Personnel.*, Rattachement.id IS NULL AS sans_rattachement
    FROM Personnel
    LEFT JOIN Rattachement ON Rattachement.id_personnel = Personnel.id
    LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
    LEFT JOIN Personnel_Nom_Historique ON Personnel_Nom_Historique.id_personnel = Personnel.id
    WHERE TRUE" . $filtrePerimetre;

// Bouton « tout voir » : tout le périmètre
if ($voirTout) {
    $requete = $pdo->prepare($texteRequete . " ORDER BY Personnel.nom, Personnel.prenom");
    $requete->execute($parametres);
    $resultats = $requete->fetchAll(PDO::FETCH_ASSOC);
// Bouton « rattachés » : seulement ceux qui ont un rattachement
} elseif ($voirRattaches) {
    $requete = $pdo->prepare($texteRequete . " AND Rattachement.id IS NOT NULL ORDER BY Personnel.nom, Personnel.prenom");
    $requete->execute($parametres);
    $resultats = $requete->fetchAll(PDO::FETCH_ASSOC);
// Recherche par nom, prénom, ancien nom, ORCID ou IdHAL
} elseif ($nomUtilisateur !== '') {
    $requete = $pdo->prepare($texteRequete . "
        AND (Personnel.nom ILIKE :nom
        OR Personnel.prenom ILIKE :nom
        OR Personnel_Nom_Historique.ancien_nom ILIKE :nom
        OR Personnel.id_orcid = :exact
        OR Personnel.id_idhal = :exact)
        ORDER BY Personnel.nom, Personnel.prenom");
    $parametres['nom'] = '%' . $nomUtilisateur . '%';
    $parametres['exact'] = $nomUtilisateur;
    $requete->execute($parametres);
    $resultats = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Rien trouvé : la vue affichera un message
    if (count($resultats) === 0) {
        $aucunResultat = true;
    }
}

// Liste les personnes qui ont le même nom et prénom (homonymes)
$requete = $pdo->query("SELECT nom, prenom FROM Personnel GROUP BY nom, prenom HAVING COUNT(*) > 1");
$homonymes = [];
foreach ($requete->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
    $homonymes[] = $ligne['nom'] . ' ' . $ligne['prenom'];
}