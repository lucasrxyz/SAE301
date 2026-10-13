<?php
require_once __DIR__ . '/config.php';

$idPersonnel = $_GET['id'] ?? null;
$personnel = null;
$personnelIntrouvable = false;
$historiqueStatuts = [];
$anciensNoms = [];
$historiqueRattachements = [];
$activites = [];
$voirContenu = verifierDroit('voir_contenu_scientifique');

if ($idPersonnel !== null) {
    [$filtrePerimetre, $parametres] = conditionPerimetre(perimetreUtilisateur($pdo));
    $parametres['id'] = $idPersonnel;

    $requete = $pdo->prepare("SELECT DISTINCT Personnel.*
        FROM Personnel
        LEFT JOIN Rattachement ON Rattachement.id_personnel = Personnel.id
        LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
        WHERE Personnel.id = :id" . $filtrePerimetre);
    $requete->execute($parametres);
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
            "SELECT r.id, r.type_rattachement, r.responsable_groupe, r.date_debut, r.date_fin,                COALESCE(g.nom, d.nom, 'Affectation inconnue') AS affectation,
                CASE WHEN r.id_groupe IS NOT NULL THEN 'Groupe' ELSE 'Département' END AS type_affectation
            FROM Rattachement r
            LEFT JOIN Groupe g ON g.id_groupe = r.id_groupe
            LEFT JOIN Departement d ON d.id_departement = COALESCE(r.id_departement, g.id_departement)
            WHERE r.id_personnel = :id
            ORDER BY r.date_debut DESC, r.id DESC"
        );
        $requete->execute(['id' => $idPersonnel]);
        $historiqueRattachements = $requete->fetchAll(PDO::FETCH_ASSOC);
        $requete = $pdo->prepare("SELECT Activite.titre, Activite.date_debut, Activite.date_fin, TypeActivite.categorie, TypeActivite.nom_type
            FROM Activite
            JOIN TypeActivite ON TypeActivite.id_type = Activite.id_type
            WHERE Activite.id_personnel = :id
            ORDER BY Activite.date_debut DESC");
        $requete->execute(['id' => $idPersonnel]);
        $activites = $requete->fetchAll(PDO::FETCH_ASSOC);
        
    }
}