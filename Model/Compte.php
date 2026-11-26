<?php
// Requêtes sur les comptes de connexion et leurs rôles
class Compte
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Cherche un compte actif avec cet identifiant
    public function trouverActif($identifiant)
    {
        $requete = $this->pdo->prepare(
            'SELECT id_compte, id_personnel, identifiant, mot_de_passe_hash
            FROM Compte
            WHERE identifiant = :identifiant AND actif = TRUE'
        );
        $requete->execute(['identifiant' => $identifiant]);
        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    // Vrai si le compte peut encore se connecter
    public function estActif($idCompte)
    {
        $requete = $this->pdo->prepare('SELECT actif FROM Compte WHERE id_compte = ?');
        $requete->execute([$idCompte]);
        return (bool) $requete->fetchColumn();
    }

    // Rôles du compte, du plus haut au plus bas
    public function roles($idCompte)
    {
        $requete = $this->pdo->prepare('SELECT Role.nom_role, Role.niveau_pyramidal
            FROM Compte_Role
            JOIN Role ON Role.id_role = Compte_Role.id_role
            WHERE Compte_Role.id_compte = ?
            ORDER BY Role.niveau_pyramidal');
        $requete->execute([$idCompte]);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Vrai si cet identifiant est déjà pris
    public function identifiantPris($identifiant)
    {
        $requete = $this->pdo->prepare('SELECT COUNT(*) FROM Compte WHERE identifiant = ?');
        $requete->execute([$identifiant]);
        return $requete->fetchColumn() > 0;
    }

    // Personnels encore présents qui n'ont pas de compte
    public function personnelsSansCompte()
    {
        $requete = $this->pdo->query('SELECT Personnel.id, Personnel.nom, Personnel.prenom, Personnel.mail
            FROM Personnel
            LEFT JOIN Compte ON Compte.id_personnel = Personnel.id
            WHERE Personnel.date_depart IS NULL AND Compte.id_compte IS NULL
            ORDER BY Personnel.nom, Personnel.prenom');
        return $requete->fetchAll();
    }

    // Rôles que l'on peut donner : le rôle d'administrateur seulement si on est administrateur
    public function rolesAttribuables($avecAdministrateur)
    {
        if ($avecAdministrateur) {
            $requete = $this->pdo->query('SELECT id_role, nom_role FROM Role ORDER BY niveau_pyramidal DESC');
        } else {
            $requete = $this->pdo->query('SELECT id_role, nom_role FROM Role WHERE niveau_pyramidal > 0 ORDER BY niveau_pyramidal DESC');
        }
        return $requete->fetchAll();
    }

    // Crée le compte avec le mot de passe crypté et lui donne un rôle
    public function creer($idPersonnel, $identifiant, $motDePasse, $idRole)
    {
        // Si une des deux écritures plante, on annule tout
        $this->pdo->beginTransaction();

        $requete = $this->pdo->prepare('INSERT INTO Compte (id_personnel, identifiant, mot_de_passe_hash, actif, date_activation) VALUES (?, ?, ?, TRUE, CURRENT_DATE) RETURNING id_compte');
        $requete->execute([$idPersonnel, $identifiant, password_hash($motDePasse, PASSWORD_DEFAULT)]);
        $idCompte = $requete->fetchColumn();

        $requete = $this->pdo->prepare('INSERT INTO Compte_Role (id_compte, id_role) VALUES (?, ?)');
        $requete->execute([$idCompte, $idRole]);

        $this->pdo->commit();
    }

    // Tous les rôles, pour le formulaire
    public function tousLesRoles()
    {
        $requete = $this->pdo->query('SELECT id_role, nom_role, niveau_pyramidal FROM Role ORDER BY niveau_pyramidal, nom_role');
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tous les comptes, avec le nom de la personne
    public function tousLesComptes()
    {
        $requete = $this->pdo->query('SELECT Compte.id_compte, Compte.identifiant, Compte.actif, Personnel.nom, Personnel.prenom
            FROM Compte
            LEFT JOIN Personnel ON Personnel.id = Compte.id_personnel
            ORDER BY Personnel.nom, Personnel.prenom');
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Rôles de chaque compte, rangés par numéro de compte
    public function rolesParCompte()
    {
        $requete = $this->pdo->query('SELECT Compte_Role.id_compte, Role.id_role, Role.nom_role
            FROM Compte_Role
            JOIN Role ON Role.id_role = Compte_Role.id_role
            ORDER BY Role.niveau_pyramidal');
        $rolesParCompte = [];
        foreach ($requete->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
            $rolesParCompte[$ligne['id_compte']][] = $ligne;
        }
        return $rolesParCompte;
    }

    // Vrai si le compte a déjà ce rôle
    public function aLeRole($idCompte, $idRole)
    {
        $requete = $this->pdo->prepare('SELECT COUNT(*) FROM Compte_Role WHERE id_compte = ? AND id_role = ?');
        $requete->execute([$idCompte, $idRole]);
        return $requete->fetchColumn() > 0;
    }

    public function ajouterRole($idCompte, $idRole)
    {
        $requete = $this->pdo->prepare('INSERT INTO Compte_Role (id_compte, id_role) VALUES (?, ?)');
        $requete->execute([$idCompte, $idRole]);
    }

    public function retirerRole($idCompte, $idRole)
    {
        $requete = $this->pdo->prepare('DELETE FROM Compte_Role WHERE id_compte = ? AND id_role = ?');
        $requete->execute([$idCompte, $idRole]);
    }

    public function nomRole($idRole)
    {
        $requete = $this->pdo->prepare('SELECT nom_role FROM Role WHERE id_role = ?');
        $requete->execute([$idRole]);
        return $requete->fetchColumn();
    }
}
