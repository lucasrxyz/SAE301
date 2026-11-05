<?php
    // Vérifie que l'utilisateur est connecté
    require_once __DIR__ . '/verifierconnexion.php';

    // Seuls le RH et l'administrateur peuvent ajouter un rattachement
    exigerDroit('gerer_personnel');

    // Le numéro du personnel vient du formulaire, sinon de l'adresse
    $idPersonnel = $_POST['id_personnel'] ?? $_GET['id'] ?? '';
    $erreurRattachement = '';
    $rattachementAjoute = isset($_GET['ajoute']);

    // Valeurs saisies, ou valeurs par défaut à l'ouverture de la page
    $idGroupeChoisi = $_POST['id_groupe'] ?? '';
    $idDepartementChoisi = $_POST['id_departement'] ?? '';
    $typeRattachementChoisi = $_POST['type_rattachement'] ?? 'principal';
    $dateDebutSaisie = $_POST['date_debut'] ?? date('Y-m-d');
    $dateFinSaisie = $_POST['date_fin'] ?? '';

    // Cherche le personnel en base
    $requete = $pdo->prepare('SELECT id, nom, prenom FROM Personnel WHERE id = ?');
    $requete->execute([(int) $idPersonnel]);
    $personnel = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$personnel) {
        $erreurRattachement = 'Personnel introuvable.';
    }

    // Liste des départements pour le formulaire
    $requete = $pdo->query('SELECT id_departement, nom, date_debut, date_fin FROM Departement ORDER BY nom');
    $departements = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Liste des groupes pour le formulaire, avec le nom de leur département
    $requete = $pdo->query('SELECT Groupe.id_groupe, Groupe.nom AS nom_groupe, Groupe.date_debut, Groupe.date_fin, Departement.nom AS nom_departement
        FROM Groupe
        JOIN Departement ON Departement.id_departement = Groupe.id_departement
        ORDER BY Departement.nom, Groupe.nom');
    $groupes = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Le code suivant s'exécute seulement quand le formulaire est envoyé
    if ($personnel && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Une liste vide veut dire « pas de groupe » ou « pas de département » (null en base)
        $idGroupe = null;
        if ($idGroupeChoisi !== '') {
            $idGroupe = (int) $idGroupeChoisi;
        }

        $idDepartement = null;
        if ($idDepartementChoisi !== '') {
            $idDepartement = (int) $idDepartementChoisi;
        }

        // Récupère les dates du groupe ou du département choisi
        $structure = false;

        if ($idGroupe !== null) {
            $nomStructure = 'Ce groupe';
            $requete = $pdo->prepare('SELECT date_debut, date_fin FROM Groupe WHERE id_groupe = ?');
            $requete->execute([$idGroupe]);
            $structure = $requete->fetch(PDO::FETCH_ASSOC);
        }

        if ($idDepartement !== null) {
            $nomStructure = 'Ce département';
            $requete = $pdo->prepare('SELECT date_debut, date_fin FROM Departement WHERE id_departement = ?');
            $requete->execute([$idDepartement]);
            $structure = $requete->fetch(PDO::FETCH_ASSOC);
        }

        // Vérifie que la date de début existe vraiment
        $dateDebut = DateTime::createFromFormat('!Y-m-d', $dateDebutSaisie);
        $debutValide = $dateDebut && $dateDebut->format('Y-m-d') === $dateDebutSaisie;

        // La date de fin peut rester vide, sinon elle doit exister vraiment
        $finValide = true;
        if ($dateFinSaisie !== '') {
            $dateFin = DateTime::createFromFormat('!Y-m-d', $dateFinSaisie);
            $finValide = $dateFin && $dateFin->format('Y-m-d') === $dateFinSaisie;
        }

        // Contrôle les données : on garde le premier message d'erreur trouvé
        if (!$debutValide || !$finValide) {
            $erreurRattachement = 'Veuillez saisir des dates valides.';
        } elseif ($dateFinSaisie !== '' && $dateFinSaisie < $dateDebutSaisie) {
            $erreurRattachement = 'La date de fin ne peut pas être antérieure à la date de début.';
        } elseif ($idGroupe === null && $idDepartement === null) {
            $erreurRattachement = 'Choisissez soit un groupe, soit un département.';
        } elseif ($idGroupe !== null && $idDepartement !== null) {
            $erreurRattachement = 'Choisissez soit un groupe, soit un département, pas les deux.';
        } elseif (!in_array($typeRattachementChoisi, ['principal', 'secondaire'])) {
            $erreurRattachement = 'Choisissez un type de rattachement valide.';
        } elseif (!$structure) {
            $erreurRattachement = 'Le groupe ou le département sélectionné est invalide.';
        } elseif ($structure['date_fin'] !== null && $structure['date_fin'] < $dateDebutSaisie) {
            $erreurRattachement = $nomStructure . ' est clôturé depuis le ' . date('d/m/Y', strtotime($structure['date_fin'])) . ', impossible d\'y rattacher quelqu\'un à cette date.';
        } elseif ($structure['date_debut'] > $dateDebutSaisie) {
            $erreurRattachement = $nomStructure . ' n\'existe qu\'à partir du ' . date('d/m/Y', strtotime($structure['date_debut'])) . ', impossible d\'y rattacher quelqu\'un avant cette date.';
        } else {
            // Une date de fin vide veut dire « sans fin » : on compare avec une date très lointaine
            $finPourComparer = $dateFinSaisie;
            if ($finPourComparer === '') {
                $finPourComparer = '9999-12-31';
            }

            // Compte les rattachements de cette personne à ce groupe qui chevauchent les dates saisies
            $requete = $pdo->prepare('SELECT COUNT(*) FROM Rattachement
                WHERE id_personnel = ? AND id_groupe = ?
                AND date_debut <= ?
                AND (date_fin IS NULL OR date_fin >= ?)');
            $requete->execute([$idPersonnel, $idGroupe, $finPourComparer, $dateDebutSaisie]);
            $dejaRattache = $requete->fetchColumn() > 0;

            if ($dejaRattache) {
                $erreurRattachement = 'Ce personnel possède déjà un rattachement à ce groupe sur une période qui chevauche les dates saisies.';
            } else {
                // En base, une date de fin vide s'enregistre avec NULL (toujours en cours)
                $dateFinEnregistree = null;
                if ($dateFinSaisie !== '') {
                    $dateFinEnregistree = $dateFinSaisie;
                }

                $requete = $pdo->prepare('INSERT INTO Rattachement (id_personnel, id_departement, id_groupe, type_rattachement, date_debut, date_fin)
                    VALUES (?, ?, ?, ?, ?, ?)');
                $requete->execute([$idPersonnel, $idDepartement, $idGroupe, $typeRattachementChoisi, $dateDebutSaisie, $dateFinEnregistree]);

                // Recharge la page avec le message de réussite
                header('Location: ajouterRattachement.php?id=' . $idPersonnel . '&ajoute=1');
                exit;
            }
        }
    }
