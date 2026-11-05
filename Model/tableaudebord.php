<?php
    // Connexion à la base de données
    require_once __DIR__ . '/config.php';

    // Périmètre et période choisis (l'année en cours par défaut)
    $choix = $_GET['choix'] ?? '';
    $dateDebut = $_GET['debut'] ?? date('Y') . '-01-01';
    $dateFin = $_GET['fin'] ?? date('Y') . '-12-31';

    // Le choix vaut par exemple 'groupe-2' : on le coupe en deux
    $perimetre = 'laboratoire';
    $idChoisi = '';
    if ($choix !== '') {
        [$perimetre, $idChoisi] = explode('-', $choix);
    }

    // Si la personne n'a pas accès à tout le labo, on lui impose son périmètre
    $perimetreAutorise = perimetreUtilisateur($pdo);
    $perimetreImpose = $perimetreAutorise['type'] !== 'laboratoire';
    if ($perimetreImpose) {
        $perimetre = $perimetreAutorise['type'];
        $idChoisi = $perimetreAutorise['id'];
    }

    // Listes pour le formulaire
    $departements = $pdo->query("SELECT id_departement, nom FROM Departement ORDER BY nom")->fetchAll();
    $groupes = $pdo->query("SELECT id_groupe, nom FROM Groupe ORDER BY nom")->fetchAll();

    // Morceau de requête : rattachements en cours sur la période
    $filtre = " AND Rattachement.date_debut <= :fin AND (Rattachement.date_fin >= :debut OR Rattachement.date_fin IS NULL)";
    $parametres = ['debut' => $dateDebut, 'fin' => $dateFin];

    // On ajoute le filtre du périmètre choisi
    if ($perimetre === 'departement') {
        $filtre .= " AND (Rattachement.id_departement = :id OR Groupe.id_departement = :id)";
        $parametres['id'] = $idChoisi;
    } elseif ($perimetre === 'groupe') {
        $filtre .= " AND Rattachement.id_groupe = :id";
        $parametres['id'] = $idChoisi;
    } elseif ($perimetre === 'personnel') {
        $filtre .= " AND Rattachement.id_personnel = :id";
        $parametres['id'] = $idChoisi;
    }

    // Personnes du périmètre
    $requete = $pdo->prepare("SELECT DISTINCT Personnel.id, Personnel.nom, Personnel.date_arrivee, Personnel.date_depart
        FROM Rattachement
        JOIN Personnel ON Personnel.id = Rattachement.id_personnel
        LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
        WHERE TRUE" . $filtre);
    $requete->execute($parametres);
    $personnes = $requete->fetchAll();

    // Compte les personnes, les arrivées et les départs sur la période
    $effectifTotal = count($personnes);
    $nombreEntrees = 0;
    $nombreSorties = 0;
    foreach ($personnes as $personne) {
        if ($personne['date_arrivee'] >= $dateDebut && $personne['date_arrivee'] <= $dateFin) {
            $nombreEntrees++;
        }
        if ($personne['date_depart'] !== null && $personne['date_depart'] >= $dateDebut && $personne['date_depart'] <= $dateFin) {
            $nombreSorties++;
        }
    }

    // Quotités rangées par statut
    $requete = $pdo->prepare("SELECT DISTINCT Personnel_Statut_Historique.id_personnel, Personnel_Statut_Historique.statut, Personnel_Statut_Historique.quotite_recherche
        FROM Personnel_Statut_Historique
        JOIN Rattachement ON Rattachement.id_personnel = Personnel_Statut_Historique.id_personnel
        LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
        WHERE Personnel_Statut_Historique.date_debut <= :fin
        AND (Personnel_Statut_Historique.date_fin >= :debut OR Personnel_Statut_Historique.date_fin IS NULL)" . $filtre);
    $requete->execute($parametres);
    $effectifsParStatut = [];
    foreach ($requete->fetchAll() as $ligne) {
        $effectifsParStatut[$ligne['statut']][] = $ligne['quotite_recherche'];
    }

    // Activités par catégorie : une activité partagée compte une fois, et une participation seulement si elle est confirmée
    $requete = $pdo->prepare("SELECT TypeActivite.categorie, COUNT(DISTINCT COALESCE(Activite.id_activite_principale, Activite.id_activite)) AS nombre
        FROM Activite
        JOIN TypeActivite ON TypeActivite.id_type = Activite.id_type
        JOIN Rattachement ON Rattachement.id_personnel = Activite.id_personnel
        LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
        WHERE (Activite.id_activite_principale IS NULL OR Activite.confirme = TRUE)
        AND Activite.date_debut::date <= :fin
        AND (Activite.date_fin >= :debut OR Activite.date_fin IS NULL)" . $filtre . "
        GROUP BY TypeActivite.categorie");
    $requete->execute($parametres);
    $activitesParCategorie = $requete->fetchAll();
    $totalActivites = array_sum(array_column($activitesParCategorie, 'nombre'));

    // Nom du périmètre à afficher en haut de la page
    $nomPerimetre = 'Tout le laboratoire';
    if ($perimetre === 'personnel') {
        $nomPerimetre = 'Mes données';
    }
    foreach ($departements as $departement) {
        if ($perimetre === 'departement' && $idChoisi == $departement['id_departement']) {
            $nomPerimetre = 'Département ' . $departement['nom'];
        }
    }
    foreach ($groupes as $groupe) {
        if ($perimetre === 'groupe' && $idChoisi == $groupe['id_groupe']) {
            $nomPerimetre = 'Groupe ' . $groupe['nom'];
        }
    }