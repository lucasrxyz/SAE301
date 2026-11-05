<?php
// Connexion à la base de données
require_once __DIR__ . '/config.php';

// Personnel demandé dans l'adresse, et valeurs vides au départ
$idPersonnel = $_GET['id'] ?? null;
$personnel = null;
$personnelIntrouvable = false;
$historiqueStatuts = [];
$anciensNoms = [];
$historiqueRattachements = [];
$activites = [];
$enseignements = [];
// Le RH ne voit pas le contenu scientifique
$voirContenu = verifierDroit('voir_contenu_scientifique');

// Le code suivant s'exécute seulement si un personnel est demandé
if ($idPersonnel !== null) {
    // Limite à ce que la personne connectée a le droit de voir
    [$filtrePerimetre, $parametres] = conditionPerimetre(perimetreUtilisateur($pdo));
    $parametres['id'] = $idPersonnel;

    // Cherche le personnel
    $requete = $pdo->prepare("SELECT DISTINCT Personnel.*
        FROM Personnel
        LEFT JOIN Rattachement ON Rattachement.id_personnel = Personnel.id
        LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
        WHERE Personnel.id = :id" . $filtrePerimetre);
    $requete->execute($parametres);
    $personnel = $requete->fetch(PDO::FETCH_ASSOC);

    // Introuvable, ou hors du périmètre autorisé
    if (!$personnel) {
        $personnelIntrouvable = true;
    } else {
        // Historique des statuts
        $requete = $pdo->prepare("SELECT statut, quotite_recherche, date_debut, date_fin FROM Personnel_Statut_Historique WHERE id_personnel = :id ORDER BY date_debut DESC");
        $requete->execute(['id' => $idPersonnel]);
        $historiqueStatuts = $requete->fetchAll(PDO::FETCH_ASSOC);

        // Anciens noms
        $requete = $pdo->prepare("SELECT ancien_nom, date_changement FROM Personnel_Nom_Historique WHERE id_personnel = :id ORDER BY date_changement DESC");
        $requete->execute(['id' => $idPersonnel]);
        $anciensNoms = $requete->fetchAll(PDO::FETCH_ASSOC);

        // Historique des rattachements, avec le nom du groupe ou du département
        $requete = $pdo->prepare(
            "SELECT r.id, r.id_groupe, r.type_rattachement, r.responsable_groupe, r.date_debut, r.date_fin,
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

        // Activités, sans les cours ni les participations refusées
        $requete = $pdo->prepare("SELECT Activite.id_activite, Activite.titre, Activite.date_debut, Activite.date_fin, Activite.description, Activite.role, TypeActivite.categorie, TypeActivite.nom_type
            FROM Activite
            JOIN TypeActivite ON TypeActivite.id_type = Activite.id_type
            WHERE Activite.id_personnel = :id
            AND TypeActivite.nom_type <> 'Cours'
            AND (Activite.confirme IS NULL OR Activite.confirme = TRUE)
            ORDER BY Activite.date_debut DESC");
        $requete->execute(['id' => $idPersonnel]);
        $activites = $requete->fetchAll(PDO::FETCH_ASSOC);

        // Enseignements : les activités de type Cours
        $requete = $pdo->prepare("SELECT Activite.titre AS intitule, Activite.date_debut, Activite.date_fin
            FROM Activite
            JOIN TypeActivite ON TypeActivite.id_type = Activite.id_type
            WHERE Activite.id_personnel = :id
            AND TypeActivite.nom_type = 'Cours'
            ORDER BY Activite.date_debut DESC, Activite.titre");
        $requete->execute(['id' => $idPersonnel]);
        $enseignements = $requete->fetchAll(PDO::FETCH_ASSOC);
    }
}