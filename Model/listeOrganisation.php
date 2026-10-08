<?php
    require_once __DIR__ . '/config.php';


    $vue = $_GET['vue'] ?? null;

    if($vue === 'departements') {
        
        $requete = $pdo->prepare(
            "SELECT
            COALESCE(Departement.nom, 'Non rattaché dans un département') AS dept_nom,
            CONCAT(Personnel.prenom, ' ', Personnel.nom) AS person_nom
            FROM Rattachement
            JOIN Personnel ON Personnel.id = Rattachement.id_personnel
            LEFT JOIN Departement ON Departement.id_departement = Rattachement.id_departement
            WHERE Rattachement.date_fin IS NULL
            ORDER BY dept_nom, person_nom;");
    
    } elseif($vue ==='groupes') {

        $requete = $pdo->prepare(
            "SELECT
            COALESCE(d_groupe.nom, d_direct.nom) AS dept_nom,
            COALESCE(Groupe.nom, 'Sans groupe') AS groupe_nom,
            CONCAT(Personnel.prenom, ' ', Personnel.nom) AS person_nom,
            CASE WHEN Rattachement.responsable_groupe THEN 'Responsable' ELSE 'Membre' END AS statut_groupe
            FROM Rattachement
            JOIN Personnel ON Personnel.id = Rattachement.id_personnel
            LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
            LEFT JOIN Departement d_groupe ON d_groupe.id_departement = Groupe.id_departement
            LEFT JOIN Departement d_direct ON d_direct.id_departement = Rattachement.id_departement
            WHERE Rattachement.date_fin IS NULL
            ORDER BY dept_nom, groupe_nom, person_nom;");

    } elseif($vue ==='hierarchie') {

        $requete = $pdo->prepare(
            "SELECT
            Role.nom_role AS nom_role,
            CONCAT(Personnel.prenom, ' ', Personnel.nom) AS person_nom,
            COALESCE(Groupe.nom, Departement.nom, 'Général') AS affectation
            FROM Role
            JOIN Compte_Role ON Compte_Role.id_role = Role.id_role
            JOIN Compte ON Compte.id_compte = Compte_Role.id_compte
            JOIN Personnel ON Personnel.id = Compte.id_personnel
            LEFT JOIN Rattachement ON (Rattachement.id_personnel = Personnel.id AND Rattachement.date_fin IS NULL)
            LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
            LEFT JOIN Departement ON Departement.id_departement = Rattachement.id_departement
            WHERE NOT Role.nom_role = 'Administrateur fonctionnel'
            ORDER BY Role.niveau_pyramidal ASC, Personnel.nom ASC;");
    } else {

    }
    if (isset($requete)) {
    $requete->execute();
    $stmt = $requete->fetchAll(PDO::FETCH_ASSOC);
    }
?>