<?php
    require_once __DIR__ . '/config.php';

    $choix = $_GET['choix'] ?? '';
    $dateDebut = $_GET['debut'] ?? date('Y') . '-01-01';
    $dateFin = $_GET['fin'] ?? date('Y') . '-12-31';

    $perimetre = 'laboratoire';
    $idChoisi = '';
    if ($choix !== '') {
        [$perimetre, $idChoisi] = explode('-', $choix);
    }

    $perimetreAutorise = perimetreUtilisateur($pdo);
    $perimetreImpose = $perimetreAutorise['type'] !== 'laboratoire';
    if ($perimetreImpose) {
        $perimetre = $perimetreAutorise['type'];
        $idChoisi = $perimetreAutorise['id'];
    }

    $departements = $pdo->query("SELECT id_departement, nom FROM Departement ORDER BY nom")->fetchAll();
    $groupes = $pdo->query("SELECT id_groupe, nom FROM Groupe ORDER BY nom")->fetchAll();

    $filtre = " AND Rattachement.date_debut <= :fin AND (Rattachement.date_fin >= :debut OR Rattachement.date_fin IS NULL)";
    $parametres = ['debut' => $dateDebut, 'fin' => $dateFin];

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

    $requete = $pdo->prepare("SELECT DISTINCT Personnel.id, Personnel.nom, Personnel.date_arrivee, Personnel.date_depart
        FROM Rattachement
        JOIN Personnel ON Personnel.id = Rattachement.id_personnel
        LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
        WHERE TRUE" . $filtre);
    $requete->execute($parametres);
    $personnes = $requete->fetchAll();

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

    $requete = $pdo->prepare("SELECT Personnel_Statut_Historique.statut, COUNT(DISTINCT Personnel_Statut_Historique.id_personnel) AS nombre
        FROM Personnel_Statut_Historique
        JOIN Rattachement ON Rattachement.id_personnel = Personnel_Statut_Historique.id_personnel
        LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
        WHERE Personnel_Statut_Historique.date_debut <= :fin
        AND (Personnel_Statut_Historique.date_fin >= :debut OR Personnel_Statut_Historique.date_fin IS NULL)" . $filtre . "
        GROUP BY Personnel_Statut_Historique.statut");
    $requete->execute($parametres);
    $effectifsParStatut = $requete->fetchAll();

    $requete = $pdo->prepare("SELECT TypeActivite.categorie, COUNT(DISTINCT Activite.id_activite) AS nombre
        FROM Activite
        JOIN TypeActivite ON TypeActivite.id_type = Activite.id_type
        JOIN Rattachement ON Rattachement.id_personnel = Activite.id_personnel
        LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
        WHERE Activite.date_debut <= :fin
        AND (Activite.date_fin >= :debut OR Activite.date_fin IS NULL)" . $filtre . "
        GROUP BY TypeActivite.categorie");
    $requete->execute($parametres);
    $activitesParCategorie = $requete->fetchAll();
    $totalActivites = array_sum(array_column($activitesParCategorie, 'nombre'));

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