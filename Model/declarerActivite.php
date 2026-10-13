<?php
    require_once __DIR__ . '/config.php';

    exigerDroit('declarer_activite');

    $erreurActivite = '';
    $activiteEnregistree = isset($_GET['enregistree']);
    $idPersonnel = $_SESSION['id_personnel'];

    $requete = $pdo->query('SELECT id_type, categorie, nom_type FROM TypeActivite WHERE est_collective = FALSE ORDER BY categorie, nom_type');
    $typesIndividuels = $requete->fetchAll(PDO::FETCH_ASSOC);

    $typeactivite = '';
    $titre = '';
    $datedebut = '';
    $datefin = '';
    $enCours = false;
    $valeursChamps = [];
    $champsDuType = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $typeactivite = $_POST['id_type'] ?? '';
        $titre = trim($_POST['titre'] ?? '');
        $datedebut = $_POST['date_debut'] ?? '';
        $datefin = $_POST['date_fin'] ?? '';
        $enCours = isset($_POST['en_cours']);
        $valeursChamps = $_POST['champs'] ?? [];

        $requete = $pdo->prepare('SELECT nom_champ, type_champ FROM TypeActivite_Champ WHERE id_type = ? ORDER BY id');
        $requete->execute([(int) $typeactivite]);
        foreach ($requete->fetchAll() as $ligne) {
            $champsDuType[$ligne['nom_champ']] = $ligne['type_champ'];
        }

        $dateFinEnregistree = $datefin === '' ? $datedebut : $datefin;
        if ($enCours) {
            $dateFinEnregistree = null;
        }

        $typeValide = in_array($typeactivite, array_column($typesIndividuels, 'id_type'));

        $champsManquants = false;
        foreach ($champsDuType as $nomChamp => $typeChamp) {
            if (trim($valeursChamps[$nomChamp] ?? '') === '') {
                $champsManquants = true;
            }
        }

        if (isset($_POST['choisir_type'])) {
            $erreurActivite = '';
        } elseif (!$typeValide) {
            $erreurActivite = 'Veuillez choisir un type d\'activité individuelle.';
        } elseif ($titre === '' || $datedebut === '') {
            $erreurActivite = 'Le titre et la date de début sont obligatoires.';
        } elseif ($champsManquants) {
            $erreurActivite = 'Veuillez remplir tous les champs propres à ce type d\'activité.';
        } elseif (!$enCours && $datefin !== '' && $datefin < $datedebut) {
            $erreurActivite = 'La date de fin doit être après la date de début.';
        } else {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('INSERT INTO Activite (id_type, id_personnel, titre, date_debut, date_fin) VALUES (?, ?, ?, ?, ?) RETURNING id_activite');
            $stmt->execute([$typeactivite, $idPersonnel, $titre, $datedebut, $dateFinEnregistree]);
            $idActivite = $stmt->fetchColumn();

            $stmt = $pdo->prepare('INSERT INTO Activite_Champ (id_activite, nom_champ, valeur) VALUES (?, ?, ?)');
            foreach ($champsDuType as $nomChamp => $typeChamp) {
                $stmt->execute([$idActivite, $nomChamp, trim($valeursChamps[$nomChamp])]);
            }

            $pdo->commit();

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

    $texteRequete = 'SELECT a.id_activite, a.titre, a.date_debut, a.date_fin, t.categorie, t.nom_type
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

    $requete = $pdo->prepare('SELECT Activite_Champ.id_activite, Activite_Champ.nom_champ, Activite_Champ.valeur
        FROM Activite_Champ
        JOIN Activite ON Activite.id_activite = Activite_Champ.id_activite
        WHERE Activite.id_personnel = ?
        ORDER BY Activite_Champ.id');
    $requete->execute([$idPersonnel]);
    $detailsActivites = [];
    foreach ($requete->fetchAll() as $ligne) {
        $detailsActivites[$ligne['id_activite']][] = $ligne['nom_champ'] . ' : ' . $ligne['valeur'];
    }

    $filtresUrl = http_build_query([
        'recherche' => $recherche,
        'type' => $filtreType,
        'etat' => $filtreEtat,
        'debut' => $filtreDebut,
        'fin' => $filtreFin
    ]);
    $ordreInverse = $ordre === 'asc' ? 'desc' : 'asc';
    $filtreActif = $recherche !== '' || $filtreType !== '' || $filtreEtat !== '' || $filtreDebut !== '' || $filtreFin !== '';