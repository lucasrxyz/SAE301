<?php
// Requêtes sur les rattachements d'un personnel à un groupe ou un département
class Rattachement
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Historique des rattachements, avec le nom du groupe ou du département
    public function historique($idPersonnel)
    {
        $requete = $this->pdo->prepare(
            "SELECT r.id, r.id_groupe, r.type_rattachement, r.responsable_groupe, r.date_debut, r.date_fin,
                COALESCE(g.nom, d.nom, 'Affectation inconnue') AS affectation,
                CASE WHEN r.id_groupe IS NOT NULL THEN 'Groupe' ELSE 'Département' END AS type_affectation
            FROM Rattachement r
            LEFT JOIN Groupe g ON g.id_groupe = r.id_groupe
            LEFT JOIN Departement d ON d.id_departement = COALESCE(r.id_departement, g.id_departement)
            WHERE r.id_personnel = :id
            ORDER BY r.date_debut DESC, r.id DESC"
        );
        $requete->execute(['id' => $idPersonnel]);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Rattachements pendant une période, pour le rapport
    public function surPeriode($idPersonnel, $dateDebut, $dateFin)
    {
        $requete = $this->pdo->prepare(
            "SELECT CASE WHEN Rattachement.id_groupe IS NOT NULL
            THEN 'Groupe' ELSE 'Département' END AS type_affectation,
            COALESCE(Groupe.nom, Departement.nom) AS nom_affectation,
            Departement.nom AS departement, Rattachement.type_rattachement,
            Rattachement.responsable_groupe, Rattachement.date_debut, Rattachement.date_fin
            FROM Rattachement
            LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
            LEFT JOIN Departement ON Departement.id_departement =
            COALESCE(Rattachement.id_departement, Groupe.id_departement)
            WHERE Rattachement.id_personnel = ?
            AND Rattachement.date_debut <= ?
            AND (Rattachement.date_fin IS NULL OR Rattachement.date_fin >= ?)
            ORDER BY Rattachement.date_debut");
        $requete->execute([$idPersonnel, $dateFin, $dateDebut]);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Vrai si la personne a déjà un rattachement à ce groupe qui chevauche les dates
    public function chevauche($idPersonnel, $idGroupe, $dateDebut, $dateFin)
    {
        $requete = $this->pdo->prepare('SELECT COUNT(*) FROM Rattachement
            WHERE id_personnel = ? AND id_groupe = ?
            AND date_debut <= ?
            AND (date_fin IS NULL OR date_fin >= ?)');
        $requete->execute([$idPersonnel, $idGroupe, $dateFin, $dateDebut]);
        return $requete->fetchColumn() > 0;
    }

    public function ajouter($idPersonnel, $idDepartement, $idGroupe, $typeRattachement, $dateDebut, $dateFin)
    {
        $requete = $this->pdo->prepare('INSERT INTO Rattachement (id_personnel, id_departement, id_groupe, type_rattachement, date_debut, date_fin)
            VALUES (?, ?, ?, ?, ?, ?)');
        $requete->execute([$idPersonnel, $idDepartement, $idGroupe, $typeRattachement, $dateDebut, $dateFin]);
    }

    // Rattachement de cette personne encore en cours
    public function enCours($idRattachement, $idPersonnel)
    {
        $requete = $this->pdo->prepare('SELECT date_debut FROM Rattachement WHERE id = ? AND id_personnel = ? AND date_fin IS NULL');
        $requete->execute([$idRattachement, $idPersonnel]);
        return $requete->fetch();
    }

    // Met la date de fin sur le rattachement
    public function cloturer($idRattachement, $dateFin)
    {
        $requete = $this->pdo->prepare('UPDATE Rattachement SET date_fin = ? WHERE id = ?');
        $requete->execute([$dateFin, $idRattachement]);
    }

    // Rattachement en cours et sans responsabilité
    public function enCoursSansResponsable($idRattachement, $idPersonnel)
    {
        $requete = $this->pdo->prepare('SELECT * FROM Rattachement WHERE id = ? AND id_personnel = ? AND date_fin IS NULL AND responsable_groupe = FALSE');
        $requete->execute([$idRattachement, $idPersonnel]);
        return $requete->fetch();
    }

    // Ferme le rattachement la veille, en crée un nouveau avec la responsabilité et donne le rôle
    public function nommerResponsable($rattachement, $idPersonnel, $dateEffet)
    {
        $this->pdo->beginTransaction();

        $requete = $this->pdo->prepare("UPDATE Rattachement SET date_fin = ?::date - 1 WHERE id = ?");
        $requete->execute([$dateEffet, $rattachement['id']]);

        $requete = $this->pdo->prepare('INSERT INTO Rattachement (id_personnel, id_departement, id_groupe, type_rattachement, responsable_groupe, date_debut) VALUES (?, ?, ?, ?, TRUE, ?)');
        $requete->execute([$idPersonnel, $rattachement['id_departement'], $rattachement['id_groupe'], $rattachement['type_rattachement'], $dateEffet]);

        // Avec un groupe : responsable de groupe, sinon responsable de département
        $nomRole = $rattachement['id_groupe'] ? 'Responsable de groupe' : 'Responsable de département';

        $requete = $this->pdo->prepare('SELECT id_compte FROM Compte WHERE id_personnel = ?');
        $requete->execute([$idPersonnel]);
        $idCompte = $requete->fetchColumn();

        $requete = $this->pdo->prepare('SELECT id_role FROM Role WHERE nom_role = ?');
        $requete->execute([$nomRole]);
        $idRole = $requete->fetchColumn();

        // Donne le rôle au compte, sauf s'il l'a déjà
        if ($idCompte && $idRole) {
            $requete = $this->pdo->prepare('INSERT INTO Compte_Role (id_compte, id_role) VALUES (?, ?) ON CONFLICT DO NOTHING');
            $requete->execute([$idCompte, $idRole]);
        }

        $this->pdo->commit();
    }
}
