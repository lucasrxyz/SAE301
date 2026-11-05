<?php
    require_once __DIR__ . '/config.php';

    exigerDroit('declarer_activite');

    $erreurActivite = '';
    $activiteEnregistree = isset($_GET['enregistree']);
    $idPorteur = $_SESSION['id_personnel'];
    $rolesPossibles = ['Co-auteur', 'Co-organisateur', 'Co-encadrant', 'Membre'];


    // Types d'activité
    $requete = $pdo->query('SELECT id_type, categorie, nom_type FROM TypeActivite ORDER BY categorie, nom_type');
    $typesActivite = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Personnels actifs, sauf moi
    $requete = $pdo->prepare('SELECT id, nom, prenom, id_idhal, id_orcid FROM Personnel WHERE actif = TRUE AND id <> ? ORDER BY nom, prenom');
    $requete->execute([$idPorteur]);
    $listePersonnels = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Valeurs par défaut du formulaire
    $typeactivite = '';
    $titre = '';
    $datedebut = '';
    $datefin = '';
    $enCours = false;
    $description = '';
    $collaborateurs = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $typeactivite = $_POST['id_type'] ?? '';
        $titre = trim($_POST['titre'] ?? '');
        $datedebut = $_POST['date_debut'] ?? '';
        $datefin = $_POST['date_fin'] ?? '';
        $enCours = isset($_POST['en_cours']);
        $description = trim($_POST['description'] ?? '');
        $collaborateurs = $_POST['collaborateurs'] ?? [];

        $dateFinEnregistree = $datefin === '' ? $datedebut : $datefin;
        if ($enCours) {
            $dateFinEnregistree = null;
        }

        // Chaque collaborateur choisi doit être dans la liste
        $collaborateurInvalide = false;
        foreach ($collaborateurs as $idCollab) {
            if (!in_array($idCollab, array_column($listePersonnels, 'id'))) {
                $collaborateurInvalide = true;
            }
        }

        if (!in_array($typeactivite, array_column($typesIndividuels, 'id_type'))) {
            $erreurActivite = 'Veuillez choisir un type d\'activité.';
        } elseif ($titre === '' || $datedebut === '') {
            $erreurActivite = 'Le titre et la date de début sont obligatoires.';
        } elseif (!$enCours && $datefin !== '' && $datefin < $datedebut) {
            $erreurActivite = 'La date de fin doit être après la date de début.';
        } elseif (count($collaborateurs) === 0) {
            $erreurActivite = 'Veuillez choisir au moins un collaborateur.';
        } elseif ($collaborateurInvalide) {
            $erreurActivite = 'Un collaborateur choisi n\'existe pas.';
        } else {
            // 1. Mon activité (je suis le porteur)
            $requete = $pdo->prepare("INSERT INTO Activite (id_type, id_personnel, titre, est_collective, description, role, confirme, date_debut, date_fin)
                VALUES (?, ?, ?, TRUE, ?, 'Porteur', TRUE, ?, ?) RETURNING id_activite");
            $requete->execute([$typeactivite, $idPersonnel, $titre, $description, $datedebut, $dateFinEnregistree]);
            $idActivitePrincipale = $requete->fetchColumn();

            // 2. Une ligne par collaborateur, reliée à mon activité
            $requete = $pdo->prepare("INSERT INTO Activite (id_type, id_personnel, titre, est_collective, description, id_activite_principale, role, date_debut, date_fin)
                VALUES (?, ?, ?, TRUE, ?, ?, 'Membre', ?, ?)");
            foreach ($collaborateurs as $idCollab) {
                $requete->execute([$typeactivite, $idCollab, $titre, $description, $idActivitePrincipale, $datedebut, $dateFinEnregistree]);
            }

            header('Location: declarerActivite.php?enregistree=1');
            exit;
        }
    }