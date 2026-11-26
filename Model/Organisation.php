<?php
// Requêtes sur les départements et les groupes
class Organisation
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function departements()
    {
        $requete = $this->pdo->query('SELECT id_departement, nom, date_debut, date_fin FROM Departement ORDER BY nom');
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    public function groupes()
    {
        $requete = $this->pdo->query('SELECT id_groupe, nom FROM Groupe ORDER BY nom');
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Groupes avec leurs dates et le nom de leur département
    public function groupesAvecDepartement()
    {
        $requete = $this->pdo->query('SELECT Groupe.id_groupe, Groupe.nom AS nom_groupe, Groupe.date_debut, Groupe.date_fin, Departement.nom AS nom_departement
            FROM Groupe
            JOIN Departement ON Departement.id_departement = Groupe.id_departement
            ORDER BY Departement.nom, Groupe.nom');
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Départements encore ouverts
    public function departementsOuverts()
    {
        $requete = $this->pdo->query('SELECT id_departement, nom FROM Departement WHERE date_fin IS NULL ORDER BY nom');
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Groupes encore ouverts
    public function groupesOuverts()
    {
        $requete = $this->pdo->query('SELECT id_groupe, nom FROM Groupe WHERE date_fin IS NULL ORDER BY nom');
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    public function datesGroupe($idGroupe)
    {
        $requete = $this->pdo->prepare('SELECT date_debut, date_fin FROM Groupe WHERE id_groupe = ?');
        $requete->execute([$idGroupe]);
        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    public function datesDepartement($idDepartement)
    {
        $requete = $this->pdo->prepare('SELECT date_debut, date_fin FROM Departement WHERE id_departement = ?');
        $requete->execute([$idDepartement]);
        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    // Groupes ouverts sur toute la période
    public function groupesSurPeriode($debut, $fin)
    {
        $requete = $this->pdo->prepare(
            "SELECT Departement.nom AS dept_nom, Groupe.nom AS groupe_nom, TO_CHAR(Groupe.date_debut,
            'DD/MM/YYYY') AS date_debut,
            COALESCE(TO_CHAR(Groupe.date_fin, 'DD/MM/YYYY'), 'En cours') AS date_fin
            FROM Groupe
            JOIN Departement ON Departement.id_departement = Groupe.id_departement
            WHERE Groupe.date_debut <= ? AND (Groupe.date_fin
            IS NULL OR Groupe.date_fin >= ?)
            ORDER BY dept_nom, groupe_nom;");
        $requete->execute([$debut, $fin]);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Organisation à une date : par département, par groupe ou par hiérarchie
    public function aLaDate($vue, $dateConsultee)
    {
        // Condition : le rattachement est en cours à la date consultée
        $actifALaDate = "Rattachement.date_debut <= :date AND (Rattachement.date_fin IS NULL OR Rattachement.date_fin >= :date)";

        if ($vue === 'departements') {
            // Une ligne par personne et par département
            $texteRequete = "SELECT DISTINCT
                COALESCE(d_groupe.nom, d_direct.nom, 'Non rattaché dans un département') AS dept_nom,
                Personnel.id, Personnel.prenom, Personnel.nom
                FROM Rattachement
                JOIN Personnel ON Personnel.id = Rattachement.id_personnel
                LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
                LEFT JOIN Departement d_groupe ON d_groupe.id_departement = Groupe.id_departement
                LEFT JOIN Departement d_direct ON d_direct.id_departement = Rattachement.id_departement
                WHERE " . $actifALaDate . "
                ORDER BY dept_nom, Personnel.nom, Personnel.prenom";
        } elseif ($vue === 'groupes') {
            // Département, groupe, personne et responsabilité
            $texteRequete = "SELECT
                COALESCE(d_groupe.nom, d_direct.nom) AS dept_nom,
                COALESCE(Groupe.nom, 'Sans groupe') AS groupe_nom,
                Personnel.prenom,
                Personnel.nom,
                Rattachement.responsable_groupe,
                Rattachement.id_groupe
                FROM Rattachement
                JOIN Personnel ON Personnel.id = Rattachement.id_personnel
                LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
                LEFT JOIN Departement d_groupe ON d_groupe.id_departement = Groupe.id_departement
                LEFT JOIN Departement d_direct ON d_direct.id_departement = Rattachement.id_departement
                WHERE " . $actifALaDate . "
                ORDER BY dept_nom, groupe_nom, Personnel.nom, Personnel.prenom";
        } elseif ($vue === 'hierarchie') {
            // Rôles des personnes présentes à cette date
            $texteRequete = "SELECT
                Role.nom_role AS nom_role,
                Personnel.prenom,
                Personnel.nom,
                COALESCE(Groupe.nom, Departement.nom, 'Général') AS affectation
                FROM Role
                JOIN Compte_Role ON Compte_Role.id_role = Role.id_role
                JOIN Compte ON Compte.id_compte = Compte_Role.id_compte
                JOIN Personnel ON Personnel.id = Compte.id_personnel
                LEFT JOIN Rattachement ON Rattachement.id_personnel = Personnel.id AND " . $actifALaDate . "
                LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
                LEFT JOIN Departement ON Departement.id_departement = Rattachement.id_departement
                WHERE NOT Role.nom_role = 'Administrateur fonctionnel'
                AND Personnel.date_arrivee <= :date AND (Personnel.date_depart IS NULL OR Personnel.date_depart >= :date)
                ORDER BY Role.niveau_pyramidal ASC, Personnel.nom ASC";
        } else {
            return [];
        }

        $requete = $this->pdo->prepare($texteRequete);
        $requete->execute(['date' => $dateConsultee]);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ajouterDepartement($nom, $dateDebut)
    {
        $requete = $this->pdo->prepare("INSERT INTO Departement (nom, date_debut) VALUES (?, ?)");
        $requete->execute([$nom, $dateDebut]);
    }

    public function modifierDepartement($idDepartement, $nom, $dateDebut, $dateFin)
    {
        $requete = $this->pdo->prepare("UPDATE Departement SET nom = ?, date_debut = ?, date_fin = ? WHERE id_departement = ?");
        $requete->execute([$nom, $dateDebut, $dateFin, $idDepartement]);
    }

    // Clôture : on met une date de fin, on ne supprime pas. Renvoie faux si la date est avant la création
    public function cloturerDepartement($idDepartement, $dateCloture)
    {
        $requete = $this->pdo->prepare("UPDATE Departement SET date_fin = ? WHERE id_departement = ? AND date_debut <= ?");
        $requete->execute([$dateCloture, $idDepartement, $dateCloture]);
        return $requete->rowCount() > 0;
    }

    public function ajouterGroupe($idDepartement, $nom, $dateDebut)
    {
        $requete = $this->pdo->prepare("INSERT INTO Groupe (id_departement, nom, date_debut) VALUES (?, ?, ?)");
        $requete->execute([$idDepartement, $nom, $dateDebut]);
    }

    public function modifierGroupe($idGroupe, $nom, $dateDebut, $dateFin)
    {
        $requete = $this->pdo->prepare("UPDATE Groupe SET nom = ?, date_debut = ?, date_fin = ? WHERE id_groupe = ?");
        $requete->execute([$nom, $dateDebut, $dateFin, $idGroupe]);
    }

    public function cloturerGroupe($idGroupe, $dateCloture)
    {
        $requete = $this->pdo->prepare("UPDATE Groupe SET date_fin = ? WHERE id_groupe = ? AND date_debut <= ?");
        $requete->execute([$dateCloture, $idGroupe, $dateCloture]);
        return $requete->rowCount() > 0;
    }
}
