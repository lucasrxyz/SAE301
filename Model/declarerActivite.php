<?php
    // Connexion à la base de données
    require_once __DIR__ . '/config.php';

    // Vérifie le droit de déclarer des activités
    exigerDroit('declarer_activite');

    // Messages et personne connectée
    $erreurActivite = '';
    $activiteEnregistree = isset($_GET['enregistree']);
    $idPersonnel = $_SESSION['id_personnel'];
    // Individuelle ou collective : choisi avec les boutons en haut de la page
    $mode = $_POST['mode'] ?? '';
    if ($mode !== 'individuelle' && $mode !== 'collective') {
        $mode = '';
    }

    // Types d'activité pour le formulaire
    $requete = $pdo->query('SELECT id_type, categorie, nom_type FROM TypeActivite ORDER BY categorie, nom_type');
    $tousLesTypes = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Collaborateurs possibles : tous les personnels actifs, sauf moi
    $requete = $pdo->prepare('SELECT id, nom, prenom, id_idhal, id_orcid FROM Personnel WHERE actif = TRUE AND id <> ? ORDER BY nom, prenom');
    $requete->execute([$idPersonnel]);
    $listePersonnels = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Valeurs du formulaire : vides à l'ouverture, gardées quand on filtre ou en cas d'erreur
    $typeactivite = $_POST['id_type'] ?? '';
    $titre = trim($_POST['titre'] ?? '');
    $datedebut = $_POST['date_debut'] ?? '';
    $datefin = $_POST['date_fin'] ?? '';
    $enCours = isset($_POST['en_cours']);
    $description = trim($_POST['description'] ?? '');
    $collaborateurs = $_POST['collaborateurs'] ?? [];

    // Filtres de la liste des collaborateurs
    $filtreNom = trim($_POST['filtre_nom'] ?? '');
    $filtreDepartement = $_POST['filtre_departement'] ?? '';
    $filtreGroupe = $_POST['filtre_groupe'] ?? '';

    // Départements et groupes encore ouverts, pour les listes de filtres
    $requete = $pdo->query('SELECT id_departement, nom FROM Departement WHERE date_fin IS NULL ORDER BY nom');
    $departementsFiltre = $requete->fetchAll(PDO::FETCH_ASSOC);

    $requete = $pdo->query('SELECT id_groupe, nom FROM Groupe WHERE date_fin IS NULL ORDER BY nom');
    $groupesFiltre = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Collaborateurs qui correspondent aux filtres, d'après leur rattachement en cours
    $texteCollaborateurs = 'SELECT DISTINCT Personnel.id, Personnel.nom, Personnel.prenom, Personnel.id_idhal, Personnel.id_orcid
        FROM Personnel
        LEFT JOIN Rattachement ON Rattachement.id_personnel = Personnel.id AND Rattachement.date_fin IS NULL
        LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
        WHERE Personnel.actif = TRUE AND Personnel.id <> :id';
    $parametresCollaborateurs = ['id' => $idPersonnel];

    if ($filtreNom !== '') {
        $texteCollaborateurs .= ' AND (Personnel.nom ILIKE :nom OR Personnel.prenom ILIKE :nom)';
        $parametresCollaborateurs['nom'] = '%' . $filtreNom . '%';
    }
    if ($filtreDepartement !== '') {
        $texteCollaborateurs .= ' AND (Rattachement.id_departement = :departement OR Groupe.id_departement = :departement)';
        $parametresCollaborateurs['departement'] = (int) $filtreDepartement;
    }
    if ($filtreGroupe !== '') {
        $texteCollaborateurs .= ' AND Rattachement.id_groupe = :groupe';
        $parametresCollaborateurs['groupe'] = (int) $filtreGroupe;
    }

    $texteCollaborateurs .= ' ORDER BY Personnel.nom, Personnel.prenom';
    $requete = $pdo->prepare($texteCollaborateurs);
    $requete->execute($parametresCollaborateurs);
    $collaborateursFiltres = $requete->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_POST['enregistrer'])) {
        // Sans date de fin, la fin vaut le début ; « en cours » veut dire pas de fin
        $dateFinEnregistree = $datefin === '' ? $datedebut : $datefin;
        if ($enCours) {
            $dateFinEnregistree = null;
        }

        // Le type doit être dans la liste
        $typeValide = in_array($typeactivite, array_column($tousLesTypes, 'id_type'));

        // Chaque collaborateur doit être dans la liste
        $collaborateurInvalide = false;
        foreach ($collaborateurs as $idCollaborateur) {
            if (!in_array($idCollaborateur, array_column($listePersonnels, 'id'))) {
                $collaborateurInvalide = true;
            }
        }

        // Contrôle les données : on garde le premier message d'erreur trouvé
        if ($mode === '') {
            $erreurActivite = 'Veuillez choisir une activité individuelle ou collective.';
        } elseif (!$typeValide) {
            $erreurActivite = 'Veuillez choisir un type d\'activité.';
        } elseif ($titre === '' || $datedebut === '') {
            $erreurActivite = 'Le titre et la date de début sont obligatoires.';
        } elseif (!$enCours && $datefin !== '' && $datefin < $datedebut) {
            $erreurActivite = 'La date de fin doit être après la date de début.';
        } elseif ($mode === 'collective' && count($collaborateurs) === 0) {
            $erreurActivite = 'Veuillez choisir au moins un collaborateur.';
        } elseif ($mode === 'collective' && $collaborateurInvalide) {
            $erreurActivite = 'Un collaborateur choisi n\'existe pas.';
        // Individuelle : une seule ligne
        } elseif ($mode === 'individuelle') {
            $stmt = $pdo->prepare('INSERT INTO Activite (id_type, id_personnel, titre, description, date_debut, date_fin) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$typeactivite, $idPersonnel, $titre, $description === '' ? null : $description, $datedebut, $dateFinEnregistree]);

            header('Location: declarerActivite.php?enregistree=1');
            exit;
        } else {
            // Collective : si une écriture plante, on annule tout
            $pdo->beginTransaction();

            // Ma ligne de porteur, déjà confirmée, et on récupère son numéro
            $stmt = $pdo->prepare("INSERT INTO Activite (id_type, id_personnel, titre, est_collective, description, role, confirme, date_debut, date_fin)
                VALUES (?, ?, ?, TRUE, ?, 'Porteur', TRUE, ?, ?) RETURNING id_activite");
            $stmt->execute([$typeactivite, $idPersonnel, $titre, $description === '' ? null : $description, $datedebut, $dateFinEnregistree]);
            $idActivitePrincipale = $stmt->fetchColumn();

            // Une ligne par collaborateur, reliée à la mienne et en attente de réponse
            $stmt = $pdo->prepare("INSERT INTO Activite (id_type, id_personnel, titre, est_collective, description, id_activite_principale, role, date_debut, date_fin)
                VALUES (?, ?, ?, TRUE, ?, ?, 'Membre', ?, ?)");
            foreach ($collaborateurs as $idCollaborateur) {
                $stmt->execute([$typeactivite, $idCollaborateur, $titre, $description === '' ? null : $description, $idActivitePrincipale, $datedebut, $dateFinEnregistree]);
            }

            $pdo->commit();

            // Recharge la page avec le message de réussite
            header('Location: declarerActivite.php?enregistree=1');
            exit;
        }
    }

    // Filtres et tri de la liste « Mes activités »
    $recherche = trim($_GET['recherche'] ?? '');
    $filtreType = $_GET['type'] ?? '';
    $filtreEtat = $_GET['etat'] ?? '';
    $filtreDebut = $_GET['debut'] ?? '';
    $filtreFin = $_GET['fin'] ?? '';
    $tri = $_GET['tri'] ?? 'date';
    $ordre = ($_GET['ordre'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

    // Colonnes de tri autorisées, pour ne pas mettre n'importe quoi dans le SQL
    $colonnesTri = [
        'date' => 'a.date_debut',
        'titre' => 'a.titre',
        'type' => 't.nom_type',
        'etat' => '(a.date_fin IS NULL OR a.date_fin >= CURRENT_DATE)'
    ];
    if (!isset($colonnesTri[$tri])) {
        $tri = 'date';
    }

    // Mes activités, sans les participations refusées
    $texteRequete = 'SELECT a.id_activite, a.titre, a.date_debut, a.date_fin, a.description, a.role, t.categorie, t.nom_type
        FROM Activite a
        JOIN TypeActivite t ON t.id_type = a.id_type
        WHERE a.id_personnel = :id
        AND (a.confirme IS NULL OR a.confirme = TRUE)';
    $parametres = ['id' => $idPersonnel];

    // Ajoute à la requête chaque filtre rempli
    if ($recherche !== '') {
        $texteRequete .= ' AND a.titre ILIKE :recherche';
        $parametres['recherche'] = '%' . $recherche . '%';
    }
    if ($filtreType !== '') {
        $texteRequete .= ' AND a.id_type = :type';
        $parametres['type'] = $filtreType;
    }
    if ($filtreEtat === 'en_cours') {
        $texteRequete .= ' AND (a.date_fin IS NULL OR a.date_fin >= CURRENT_DATE)';
    } elseif ($filtreEtat === 'terminee') {
        $texteRequete .= ' AND a.date_fin < CURRENT_DATE';
    }
    if ($filtreDebut !== '') {
        $texteRequete .= ' AND (a.date_fin >= :debut OR a.date_fin IS NULL)';
        $parametres['debut'] = $filtreDebut;
    }
    if ($filtreFin !== '') {
        $texteRequete .= ' AND a.date_debut::date <= :fin';
        $parametres['fin'] = $filtreFin;
    }

    // Ajoute le tri puis lance la requête
    $texteRequete .= ' ORDER BY ' . $colonnesTri[$tri] . ' ' . $ordre;

    $requete = $pdo->prepare($texteRequete);
    $requete->execute($parametres);
    $mesActivites = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Prépare les liens de tri, qui gardent les filtres en cours
    $filtresUrl = http_build_query([
        'recherche' => $recherche,
        'type' => $filtreType,
        'etat' => $filtreEtat,
        'debut' => $filtreDebut,
        'fin' => $filtreFin
    ]);
    $ordreInverse = $ordre === 'asc' ? 'desc' : 'asc';
    $filtreActif = $recherche !== '' || $filtreType !== '' || $filtreEtat !== '' || $filtreDebut !== '' || $filtreFin !== '';