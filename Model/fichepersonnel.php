<?php
require_once __DIR__ . '/config.php';

$idPersonnel = $_GET['id'] ?? null;
$personnel = null;
$personnelIntrouvable = false;
$historiqueStatuts = [];
$anciensNoms = [];
$historiqueRattachements = [];

if ($idPersonnel !== null) {
    $requete = $pdo->prepare("SELECT * FROM Personnel WHERE id = :id");
    $requete->execute(['id' => $idPersonnel]);
    $personnel = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$personnel) {
        $personnelIntrouvable = true;
    } else {
        $requete = $pdo->prepare("SELECT statut, quotite_recherche, date_debut, date_fin FROM Personnel_Statut_Historique WHERE id_personnel = :id ORDER BY date_debut DESC");
        $requete->execute(['id' => $idPersonnel]);
        $historiqueStatuts = $requete->fetchAll(PDO::FETCH_ASSOC);

        $requete = $pdo->prepare("SELECT ancien_nom, date_changement FROM Personnel_Nom_Historique WHERE id_personnel = :id ORDER BY date_changement DESC");
        $requete->execute(['id' => $idPersonnel]);
        $anciensNoms = $requete->fetchAll(PDO::FETCH_ASSOC);

        $requete = $pdo->prepare(
            "SELECT r.id, r.type_rattachement, r.date_debut, r.date_fin,
                COALESCE(g.nom, d.nom, 'Affectation inconnue') AS affectation,
                CASE WHEN r.id_groupe IS NOT NULL THEN 'Groupe' ELSE 'Département' END AS type_affectation
            FROM Rattachement r
            LEFT JOIN Groupe g ON g.id_groupe = r.id_groupe
            LEFT JOIN Departement d ON d.id_departement = COALESCE(r.id_departement, g.id_departement)
            WHERE r.id_personnel = :id
            ORDER BY r.date_debut DESC, r.id DESC"
        );
        $requete->execute(['id' => $idPersonnel]);
        $historiqueRattachements = $requete->fetchAll(PDO::FETCH_ASSOC);
    }
}