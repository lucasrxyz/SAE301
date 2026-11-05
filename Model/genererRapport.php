<?php
    require_once __DIR__ . '/config.php';

    // Seuls les utilisateurs ayant ce droit peuvent générer leur rapport
    exigerDroit('declarer_activite');

     // Valeurs par défaut : utilisées par la Vue au premier affichage
    // avant que le formulaire ne soit envoyé
        $erreurRapport = '';
        $dateDebut = '';
        $dateFin = '';


    if (isset($_POST['enregistrer'])) {
        // Données du formulaire
        $idPersonnel = $_SESSION['id_personnel'];
        $dateDebut = $_POST['date_debut'];
        $dateFin = $_POST['date_fin'];
        $format = $_POST['formatRapport'];

        // Règle utilisée dans toutes les requêtes ci-dessous :
        // un élément est sur la période s'il commence AVANT la fin de la
        // période ET se termine APRÈS son début (ou n'est pas terminé).
        // D'où l'ordre des paramètres : [$idPersonnel, $dateFin, $dateDebut]

        // 1. Fiche d'identité de la personne (une seule ligne -> fetch)
        $requete = $pdo->prepare(
            "SELECT id, nom, prenom, mail, id_orcid, id_idhal, date_arrivee, date_depart 
            FROM Personnel WHERE id = ?");
        $requete->execute([$idPersonnel]);
        $personnel = $requete->fetch(PDO::FETCH_ASSOC);

        // 2. Statuts occupés pendant la période
        $requete = $pdo->prepare(
            "SELECT statut, quotite_recherche, date_debut, date_fin 
            FROM Personnel_Statut_Historique 
            WHERE id_personnel = ? 
            AND date_debut <= ? 
            AND (date_fin IS NULL OR date_fin >= ?) 
            ORDER BY date_debut");
        $requete->execute([$idPersonnel, $dateFin, $dateDebut]);
        $statuts = $requete->fetchAll(PDO::FETCH_ASSOC);

        // 3. Rattachements (groupe ou département) pendant la période
        // type_affectation : 'Groupe' ou 'Département'
        // nom_affectation  : nom du groupe, sinon nom du département
        // le 2e COALESCE retrouve le département d'un groupe
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


        // 4. Activités de la personne pendant la période
        // On garde ses propres activités et ses participations confirmées
        // à une activité collective (pas les invitations en attente)
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

        // Rangement des activités par catégorie, par exemple :
        // $activitesParCategorie['Publication'] = [activité 1, activité 2, ...]
        // Utilisé par le gabarit Word (une section par catégorie) et par le CSV
        $activitesParCategorie = [];
        foreach ($requete->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
            $activitesParCategorie[$ligne['categorie']][] = $ligne;
        }


        // Envoi du fichier selon le format choisi
        if ($format ==='word') {

            // FORMAT WORD : le gabarit HTML est envoyé avec l'extension .doc,
            // Word et LibreOffice l'ouvrent comme un document modifiable
            header('Content-Type: application/msword; charset=UTF-8');
            header('Content-Disposition: attachment; filename="rapport.doc"');
            // Ligne pour lire les accents
            echo "\xEF\xBB\xBF";

            // Le gabarit utilise $personnel, $statuts, $rattachements,
            // $activitesParCategorie, $dateDebut et $dateFin
            require __DIR__ . '/gabaritRapport.php';
            exit;

        } elseif ($format ==='csv') {
            // FORMAT CSV : une ligne par activité, lisible dans Excel
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="rapport.csv"');
            // Ligne pour lire les accents
            echo "\xEF\xBB\xBF";

            // php://output = on écrit directement dans la réponse envoyée
            // au navigateur, aucun fichier n'est créé sur le serveur
            $sortie = fopen('php://output', 'w');

            // Ligne d'en-tête des colonnes
            // Paramètres de fputcsv : séparateur ';' (Excel français),
            // guillemets '"' et échappement '\\' (valeurs par défaut,
            // écrites pour éviter un avertissement de PHP 8.4)
            fputcsv($sortie, ['Catégorie', 'Type', 'Titre', 'Date début', 'Date fin'], ';', '"', '\\');

            if ($activitesParCategorie) {
                // Une ligne par activité, catégorie par catégorie
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
                // Aucune activité sur la période
                fputcsv($sortie, ['Aucune donnée sur la période.'], ';', '"', '\\');
            }

            fclose($sortie);
            exit;
        } else {
            // Format inconnu : la page se réaffiche avec ce message
            $erreurRapport = 'Veuillez choisir un format valide.';
        }
}