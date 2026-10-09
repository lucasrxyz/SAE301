<?php
    require_once __DIR__ . '/config.php';

    $erreurActivite = '';
    $activiteEnregistree = isset($_GET['enregistree']);
    $idPersonnel = $_SESSION['id_personnel'];

    $requete = $pdo->query('SELECT id_type, categorie, nom_type FROM TypeActivite WHERE est_collective = FALSE ORDER BY categorie, nom_type');
    $typesIndividuels = $requete->fetchAll(PDO::FETCH_ASSOC);

    $typeactivite = '';
    $titre = '';
    $datedebut = '';
    $datefin = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $typeactivite = $_POST['id_type'] ?? '';
        $titre = trim($_POST['titre'] ?? '');
        $datedebut = $_POST['date_debut'] ?? '';
        $datefin = $_POST['date_fin'] ?? '';

        $typeValide = in_array($typeactivite, array_column($typesIndividuels, 'id_type'));

        if (!$typeValide) {
            $erreurActivite = 'Veuillez choisir un type d\'activité individuelle.';
        } elseif ($titre === '' || $datedebut === '') {
            $erreurActivite = 'Le titre et la date de début sont obligatoires.';
        } elseif ($datefin !== '' && $datefin < $datedebut) {
            $erreurActivite = 'La date de fin doit être après la date de début.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO Activite (id_type, id_personnel, titre, date_debut, date_fin) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$typeactivite, $idPersonnel, $titre, $datedebut, $datefin === '' ? null : $datefin]);

            header('Location: declarerActivite.php?enregistree=1');
            exit;
        }
    }

    $requete = $pdo->query('SELECT id_type, categorie, nom_type FROM TypeActivite ORDER BY categorie, nom_type');
    $tousLesTypes = $requete->fetchAll(PDO::FETCH_ASSOC);

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

    $texteRequete = 'SELECT a.titre, a.date_debut, a.date_fin, t.categorie, t.nom_type
        FROM Activite a
        JOIN TypeActivite t ON t.id_type = a.id_type
        WHERE a.id_personnel = :id';
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
        $texteRequete .= ' AND a.date_debut <= :fin';
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