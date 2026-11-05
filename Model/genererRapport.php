<?php
    require_once __DIR__ . '/config.php';

    exigerDroit('declarer_activite');
        $erreurRapport = '';
        $dateDebut = '';
        $dateFin = '';


    if (isset($_POST['enregistrer'])) {
        $idPersonnel = $_SESSION['id_personnel'];
        $dateDebut = $_POST['date_debut'];
        $dateFin = $_POST['date_fin'];
        $format = $_POST['formatRapport'];

        $requete = $pdo->prepare(
            "SELECT id, nom, prenom, mail, id_orcid, id_idhal, date_arrivee, date_depart 
            FROM Personnel WHERE id = ?");
        $requete->execute([$idPersonnel]);
        $personnel = $requete->fetch(PDO::FETCH_ASSOC);

        $requete = $pdo->prepare(
            "SELECT statut, quotite_recherche, date_debut, date_fin 
            FROM Personnel_Statut_Historique 
            WHERE id_personnel = ? 
            AND date_debut <= ? 
            AND (date_fin IS NULL OR date_fin >= ?) 
            ORDER BY date_debut");
        $requete->execute([$idPersonnel, $dateFin, $dateDebut]);
        $statuts = $requete->fetchAll(PDO::FETCH_ASSOC);

        $requete = $pdo->prepare(
            "SELECT CASE WHEN Rattachement.id_groupe IS NOT NULL
            THEN 'Groupe' ELSE 'Département' END AS type_affectation,
            COALESCE(Groupe.nom, Departement.nom) AS nom_affectation,
            Departement.nom AS departement, Rattachement.type_rattachement, 
            Rattachement.responsable_groupe, Rattachement.date_debut, Rattachement.date_fin
            FROM Rattachement
            LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
            LEFT JOIN Departement ON Departement.id_departement =
            COALESCE(Rattachement.id_departement, Groupe.id_departement)
            WHERE Rattachement.id_personnel = ?
            AND Rattachement.date_debut <= ?
            AND (Rattachement.date_fin IS NULL OR Rattachement.date_fin >= ?)
            ORDER BY Rattachement.date_debut");
        $requete->execute([$idPersonnel, $dateFin, $dateDebut]);
        $rattachements = $requete->fetchAll(PDO::FETCH_ASSOC);

        $requete = $pdo->prepare(
            "SELECT TypeActivite.categorie, TypeActivite.nom_type,
            Activite.titre, Activite.role, Activite.description,
            Activite.date_debut, Activite.date_fin
            FROM Activite
            JOIN TypeActivite ON TypeActivite.id_type = Activite.id_type
            WHERE Activite.id_personnel = ?
            AND (Activite.id_activite_principale IS NULL OR Activite.confirme = TRUE)
            AND Activite.date_debut <= ?
            AND (Activite.date_fin IS NULL OR Activite.date_fin >= ?)
            ORDER BY TypeActivite.categorie, Activite.date_debut");
        $requete->execute([$idPersonnel, $dateFin, $dateDebut]);
        $activitesParCategorie = [];
        foreach ($requete->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
            $activitesParCategorie[$ligne['categorie']][] = $ligne;
        }

        if ($format ==='word') {
            header('Content-Type: application/msword; charset=UTF-8');
            header('Content-Disposition: attachment; filename="rapport.doc"');
            // Ligne pour lire les accents
            echo "\xEF\xBB\xBF";
            require __DIR__ . '/gabarit_rapport.php';
            exit;

        } elseif ($format ==='csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="rapport.csv"');
            // Ligne pour lire les accents
            echo "\xEF\xBB\xBF";

            $sortie = fopen('php://output', 'w');
            fputcsv($sortie, ['Catégorie', 'Type', 'Titre', 'Date début', 'Date fin'], ';', '"', '\\');

            if ($activitesParCategorie) {
                foreach ($activitesParCategorie as $categorie => $activites) {
                    foreach ($activites as $activite) {
                        fputcsv($sortie, [
                            $categorie,
                            $activite['nom_type'],
                            $activite['titre'],
                            date('d/m/Y', strtotime($activite['date_debut'])),
                            $activite['date_fin'] === null ? 'En cours' : date('d/m/Y', strtotime($activite['date_fin']))
                        ], ';', '"', '\\');
                    }
                }
            } else {
                fputcsv($sortie, ['Aucune donnée sur la période.'], ';', '"', '\\');
            }

            fclose($sortie);
            exit;
        } else {
            $erreurRapport = 'Veuillez choisir un format valide.';
        }
}