<?php
    require_once __DIR__ . '/config.php';

    $perimetre = $_GET['perimetre'] ?? 'laboratoire';
    $idChoisi = $_GET['id'] ?? '';
    $dateDebut = $_GET['debut'] ?? date('Y') . '-01-01';
    $dateFin = $_GET['fin'] ?? date('Y') . '-12-31';

    $requete = $pdo->query("SELECT id_departement, nom FROM Departement ORDER BY nom");
    $departements = $requete->fetchAll(PDO::FETCH_ASSOC);

    $requete = $pdo->query("SELECT id_groupe, nom FROM Groupe ORDER BY nom");
    $groupes = $requete->fetchAll(PDO::FETCH_ASSOC);

    $filtre = " AND Rattachement.date_debut <= :fin
        AND (Rattachement.date_fin >= :debut OR Rattachement.date_fin IS NULL)";
    $parametres = ['debut' => $dateDebut, 'fin' => $dateFin];

    if ($perimetre === 'departement' && $idChoisi !== '') {
        $filtre .= " AND (Rattachement.id_departement = :id OR Groupe.id_departement = :id)";
        $parametres['id'] = $idChoisi;
    } elseif ($perimetre === 'groupe' && $idChoisi !== '') {
        $filtre .= " AND Rattachement.id_groupe = :id";
        $parametres['id'] = $idChoisi;
    }

    $requete = $pdo->prepare("SELECT DISTINCT Personnel.id, Personnel.nom, Personnel.date_arrivee, Personnel.date_depart
        FROM Rattachement
        JOIN Personnel ON Personnel.id = Rattachement.id_personnel
        LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
        WHERE TRUE" . $filtre);
    $requete->execute($parametres);
    $personnes = $requete->fetchAll(PDO::FETCH_ASSOC);

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
    $effectifsParStatut = $requete->fetchAll(PDO::FETCH_ASSOC);

    $requete = $pdo->prepare("SELECT TypeActivite.categorie, COUNT(DISTINCT Activite.id_activite) AS nombre
        FROM Activite
        JOIN TypeActivite ON TypeActivite.id_type = Activite.id_type
        JOIN Rattachement ON Rattachement.id_personnel = Activite.id_personnel
        LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
        WHERE Activite.date_debut <= :fin
        AND (Activite.date_fin >= :debut OR Activite.date_fin IS NULL)" . $filtre . "
        GROUP BY TypeActivite.categorie");
    $requete->execute($parametres);
    $activitesParCategorie = $requete->fetchAll(PDO::FETCH_ASSOC);