<?php
    require_once __DIR__ . '/verifierconnexion.php';

    exigerDroit('gerer_personnel');

    $idPersonnel = $_POST['id_personnel'] ?? $_GET['id'] ?? '';
    $erreurRattachement = '';
    $rattachementAjoute = isset($_GET['ajoute']);
    $idGroupeChoisi = $_POST['id_groupe'] ?? '';
    $idDepartementChoisi = $_POST['id_departement'] ?? '';
    $typeRattachementChoisi = $_POST['type_rattachement'] ?? 'principal';
    $dateDebutSaisie = $_POST['date_debut'] ?? date('Y-m-d');
    $dateFinSaisie = $_POST['date_fin'] ?? '';

    $requete = $pdo->prepare('SELECT id, nom, prenom FROM Personnel WHERE id = ?');
    $requete->execute([(int) $idPersonnel]);
    $personnel = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$personnel) {
        $erreurRattachement = 'Personnel introuvable.';
    }

    $requete = $pdo->query('SELECT id_departement, nom FROM Departement ORDER BY nom');
    $departements = $requete->fetchAll(PDO::FETCH_ASSOC);

    $requete = $pdo->query('SELECT Groupe.id_groupe, Groupe.nom AS nom_groupe, Groupe.date_fin, Departement.nom AS nom_departement
        FROM Groupe
        JOIN Departement ON Departement.id_departement = Groupe.id_departement
        ORDER BY Departement.nom, Groupe.nom');
    $groupes = $requete->fetchAll(PDO::FETCH_ASSOC);

    if ($personnel && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $dateDebut = DateTime::createFromFormat('!Y-m-d', $dateDebutSaisie);
        $dateFin = DateTime::createFromFormat('!Y-m-d', $dateFinSaisie);
        $debutValide = $dateDebut && $dateDebut->format('Y-m-d') === $dateDebutSaisie;
        $finValide = $dateFinSaisie === '' || ($dateFin && $dateFin->format('Y-m-d') === $dateFinSaisie);

        $idGroupe = $idGroupeChoisi !== '' ? (int) $idGroupeChoisi : null;
        $idDepartement = $idDepartementChoisi !== '' ? (int) $idDepartementChoisi : null;

        $requete = $pdo->prepare('SELECT date_fin FROM Groupe WHERE id_groupe = ?');
        $requete->execute([$idGroupe ?? 0]);
        $groupeChoisi = $requete->fetch(PDO::FETCH_ASSOC);
        $groupeExiste = $groupeChoisi !== false;
        $groupeCloture = $groupeExiste && $groupeChoisi['date_fin'] !== null && $groupeChoisi['date_fin'] < $dateDebutSaisie;

        $requete = $pdo->prepare('SELECT COUNT(*) FROM Departement WHERE id_departement = ?');
        $requete->execute([$idDepartement ?? 0]);
        $departementExiste = $requete->fetchColumn() > 0;

        $texteChevauchement = 'SELECT COUNT(*) FROM Rattachement
            WHERE id_personnel = ? AND id_groupe = ?
            AND (date_fin IS NULL OR date_fin >= ?)';
        $parametresChevauchement = [$idPersonnel, $idGroupe ?? 0, $debutValide ? $dateDebutSaisie : date('Y-m-d')];
        if ($dateFinSaisie !== '' && $finValide) {
            $texteChevauchement .= ' AND date_debut <= ?';
            $parametresChevauchement[] = $dateFinSaisie;
        }
        $requete = $pdo->prepare($texteChevauchement);
        $requete->execute($parametresChevauchement);
        $dejaRattache = $requete->fetchColumn() > 0;

        if (!$debutValide || !$finValide) {
            $erreurRattachement = 'Veuillez saisir des dates valides.';
        } elseif ($dateFinSaisie !== '' && $dateFinSaisie < $dateDebutSaisie) {
            $erreurRattachement = 'La date de fin ne peut pas être antérieure à la date de début.';
        } elseif (($idGroupe === null) === ($idDepartement === null)) {
            $erreurRattachement = 'Choisissez soit un groupe, soit un département.';
        } elseif (!in_array($typeRattachementChoisi, ['principal', 'secondaire'])) {
            $erreurRattachement = 'Choisissez un type de rattachement valide.';
        } elseif (($idGroupe !== null && !$groupeExiste) || ($idDepartement !== null && !$departementExiste)) {
            $erreurRattachement = 'Le groupe ou le département sélectionné est invalide.';
        } elseif ($idGroupe !== null && $groupeCloture) {
            $erreurRattachement = 'Ce groupe est clôturé depuis le ' . date('d/m/Y', strtotime($groupeChoisi['date_fin'])) . ', impossible d\'y rattacher quelqu\'un à cette date.';
        } elseif ($idGroupe !== null && $dejaRattache) {
            $erreurRattachement = 'Ce personnel possède déjà un rattachement à ce groupe sur une période qui chevauche les dates saisies.';
        } else {
            $requete = $pdo->prepare('INSERT INTO Rattachement (id_personnel, id_departement, id_groupe, type_rattachement, date_debut, date_fin)
                VALUES (?, ?, ?, ?, ?, ?)');
            $requete->execute([$idPersonnel, $idDepartement, $idGroupe, $typeRattachementChoisi, $dateDebutSaisie, $dateFinSaisie === '' ? null : $dateFinSaisie]);

            header('Location: ajouterRattachement.php?id=' . $idPersonnel . '&ajoute=1');
            exit;
        }
    }