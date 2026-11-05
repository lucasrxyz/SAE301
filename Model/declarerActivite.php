<?php
    require_once __DIR__ . '/config.php';

    exigerDroit('declarer_activite');

    $erreurActivite = '';
    $activiteEnregistree = isset($_GET['enregistree']);
    $idPersonnel = $_SESSION['id_personnel'];
    $mode = $_POST['mode'] ?? $_GET['mode'] ?? '';
    if ($mode !== 'individuelle' && $mode !== 'collective') {
        $mode = '';
    }

    $requete = $pdo->query('SELECT id_type, categorie, nom_type FROM TypeActivite ORDER BY categorie, nom_type');
    $tousLesTypes = $requete->fetchAll(PDO::FETCH_ASSOC);

    $requete = $pdo->prepare('SELECT id, nom, prenom, id_idhal, id_orcid FROM Personnel WHERE actif = TRUE AND id_idhal IS NOT NULL AND id_orcid IS NOT NULL AND id <> ? ORDER BY nom, prenom');
    $requete->execute([$idPersonnel]);
    $listePersonnels = $requete->fetchAll(PDO::FETCH_ASSOC);

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

        $typeValide = in_array($typeactivite, array_column($tousLesTypes, 'id_type'));

        $collaborateurInvalide = false;
        foreach ($collaborateurs as $idCollaborateur) {
            if (!in_array($idCollaborateur, array_column($listePersonnels, 'id'))) {
                $collaborateurInvalide = true;
            }
        }

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
        } elseif ($mode === 'individuelle') {
            $stmt = $pdo->prepare('INSERT INTO Activite (id_type, id_personnel, titre, description, date_debut, date_fin) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$typeactivite, $idPersonnel, $titre, $description === '' ? null : $description, $datedebut, $dateFinEnregistree]);

            header('Location: declarerActivite.php?enregistree=1');
            exit;
        } else {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO Activite (id_type, id_personnel, titre, est_collective, description, role, confirme, date_debut, date_fin)
                VALUES (?, ?, ?, TRUE, ?, 'Porteur', TRUE, ?, ?) RETURNING id_activite");
            $stmt->execute([$typeactivite, $idPersonnel, $titre, $description === '' ? null : $description, $datedebut, $dateFinEnregistree]);
            $idActivitePrincipale = $stmt->fetchColumn();

            $stmt = $pdo->prepare("INSERT INTO Activite (id_type, id_personnel, titre, est_collective, description, id_activite_principale, role, date_debut, date_fin)
                VALUES (?, ?, ?, TRUE, ?, ?, 'Membre', ?, ?)");
            foreach ($collaborateurs as $idCollaborateur) {
                $stmt->execute([$typeactivite, $idCollaborateur, $titre, $description === '' ? null : $description, $idActivitePrincipale, $datedebut, $dateFinEnregistree]);
            }

            $pdo->commit();

            header('Location: declarerActivite.php?enregistree=1');
            exit;
        }
    }

    $recherche = trim($_GET['recherche'] ?? '');
    $filtreType = $_GET['type'] ?? '';
    $filtreEtat = $_GET['etat'] ?? '';
    $filtreDebut = $_GET['debut'] ?? '';
    $filtreFin = $_GET['fin'] ?? '';
    $tri = $_GET['tri'] ?? 'date';
    $ordre = ($_GET['ordre'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

    $colonnesTri = [
        'date' => 'a.date_debut',
        'titre' => 'a.titre',
        'type' => 't.nom_type',
        'etat' => '(a.date_fin IS NULL OR a.date_fin >= CURRENT_DATE)'
    ];
    if (!isset($colonnesTri[$tri])) {
        $tri = 'date';
    }

    $texteRequete = 'SELECT a.id_activite, a.titre, a.date_debut, a.date_fin, a.description, a.role, t.categorie, t.nom_type
        FROM Activite a
        JOIN TypeActivite t ON t.id_type = a.id_type
        WHERE a.id_personnel = :id
        AND (a.confirme IS NULL OR a.confirme = TRUE)';
    $parametres = ['id' => $idPersonnel];

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

    $texteRequete .= ' ORDER BY ' . $colonnesTri[$tri] . ' ' . $ordre;

    $requete = $pdo->prepare($texteRequete);
    $requete->execute($parametres);
    $mesActivites = $requete->fetchAll(PDO::FETCH_ASSOC);

    $filtresUrl = http_build_query([
        'recherche' => $recherche,
        'type' => $filtreType,
        'etat' => $filtreEtat,
        'debut' => $filtreDebut,
        'fin' => $filtreFin
    ]);
    $ordreInverse = $ordre === 'asc' ? 'desc' : 'asc';
    $filtreActif = $recherche !== '' || $filtreType !== '' || $filtreEtat !== '' || $filtreDebut !== '' || $filtreFin !== '';