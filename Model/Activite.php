<?php
// Requêtes sur les activités, les participations et les types d'activité
class Activite
{
    // Colonnes de tri autorisées, pour ne pas mettre n'importe quoi dans le SQL
    const COLONNES_TRI = [
        'date' => 'a.date_debut',
        'titre' => 'a.titre',
        'type' => 't.nom_type',
        'etat' => '(a.date_fin IS NULL OR a.date_fin >= CURRENT_DATE)'
    ];

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Types d'activité pour le formulaire
    public function types()
    {
        $requete = $this->pdo->query('SELECT id_type, categorie, nom_type FROM TypeActivite ORDER BY categorie, nom_type');
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Numéro d'un type d'activité à partir de son nom
    public function idType($nomType)
    {
        $requete = $this->pdo->prepare('SELECT id_type FROM TypeActivite WHERE nom_type = ?');
        $requete->execute([$nomType]);
        return $requete->fetchColumn();
    }

    // Activité individuelle : une seule ligne
    public function declarerIndividuelle($idType, $idPersonnel, $titre, $description, $dateDebut, $dateFin)
    {
        $requete = $this->pdo->prepare('INSERT INTO Activite (id_type, id_personnel, titre, description, date_debut, date_fin) VALUES (?, ?, ?, ?, ?, ?)');
        $requete->execute([$idType, $idPersonnel, $titre, $description === '' ? null : $description, $dateDebut, $dateFin]);
    }

    // Activité collective : ma ligne de porteur, puis une ligne par collaborateur en attente de réponse
    public function declarerCollective($idType, $idPersonnel, $titre, $description, $dateDebut, $dateFin, $collaborateurs)
    {
        $this->pdo->beginTransaction();

        $requete = $this->pdo->prepare("INSERT INTO Activite (id_type, id_personnel, titre, est_collective, description, role, confirme, date_debut, date_fin)
            VALUES (?, ?, ?, TRUE, ?, 'Porteur', TRUE, ?, ?) RETURNING id_activite");
        $requete->execute([$idType, $idPersonnel, $titre, $description === '' ? null : $description, $dateDebut, $dateFin]);
        $idActivitePrincipale = $requete->fetchColumn();

        $requete = $this->pdo->prepare("INSERT INTO Activite (id_type, id_personnel, titre, est_collective, description, id_activite_principale, role, date_debut, date_fin)
            VALUES (?, ?, ?, TRUE, ?, ?, 'Membre', ?, ?)");
        foreach ($collaborateurs as $idCollaborateur) {
            $requete->execute([$idType, $idCollaborateur, $titre, $description === '' ? null : $description, $idActivitePrincipale, $dateDebut, $dateFin]);
        }

        $this->pdo->commit();
    }

    // Mes activités avec les filtres et le tri choisis, sans les participations refusées
    public function mesActivites($idPersonnel, $recherche, $filtreType, $filtreEtat, $filtreDebut, $filtreFin, $tri, $ordre)
    {
        $texteRequete = 'SELECT a.id_activite, a.titre, a.date_debut, a.date_fin, a.description, a.role, t.categorie, t.nom_type
            FROM Activite a
            JOIN TypeActivite t ON t.id_type = a.id_type
            WHERE a.id_personnel = :id
            AND (a.confirme IS NULL OR a.confirme = TRUE)';
        $parametres = ['id' => $idPersonnel];

        // Ajoute à la requête chaque filtre rempli
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
            $texteRequete .= ' AND a.date_debut::date <= :fin';
            $parametres['fin'] = $filtreFin;
        }

        $texteRequete .= ' ORDER BY ' . self::COLONNES_TRI[$tri] . ' ' . $ordre;

        $requete = $this->pdo->prepare($texteRequete);
        $requete->execute($parametres);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Activités d'un personnel pour sa fiche, sans les cours ni les participations refusées
    public function activitesFiche($idPersonnel)
    {
        $requete = $this->pdo->prepare("SELECT Activite.id_activite, Activite.titre, Activite.date_debut, Activite.date_fin, Activite.description, Activite.role, TypeActivite.categorie, TypeActivite.nom_type
            FROM Activite
            JOIN TypeActivite ON TypeActivite.id_type = Activite.id_type
            WHERE Activite.id_personnel = :id
            AND TypeActivite.nom_type <> 'Cours'
            AND (Activite.confirme IS NULL OR Activite.confirme = TRUE)
            ORDER BY Activite.date_debut DESC");
        $requete->execute(['id' => $idPersonnel]);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Enseignements : les activités de type Cours
    public function enseignementsFiche($idPersonnel)
    {
        $requete = $this->pdo->prepare("SELECT Activite.titre AS intitule, Activite.date_debut, Activite.date_fin
            FROM Activite
            JOIN TypeActivite ON TypeActivite.id_type = Activite.id_type
            WHERE Activite.id_personnel = :id
            AND TypeActivite.nom_type = 'Cours'
            ORDER BY Activite.date_debut DESC, Activite.titre");
        $requete->execute(['id' => $idPersonnel]);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Activités pendant une période, rangées par catégorie, pour le rapport
    public function surPeriodeParCategorie($idPersonnel, $dateDebut, $dateFin)
    {
        $requete = $this->pdo->prepare(
            "SELECT TypeActivite.categorie, TypeActivite.nom_type,
            Activite.titre, Activite.role, Activite.description,
            Activite.date_debut, Activite.date_fin
            FROM Activite
            JOIN TypeActivite ON TypeActivite.id_type = Activite.id_type
            WHERE Activite.id_personnel = ?
            AND (Activite.id_activite_principale IS NULL OR Activite.confirme = TRUE)
            AND Activite.date_debut <= ?
            AND (Activite.date_fin IS NULL OR Activite.date_fin >= ?)
            ORDER BY TypeActivite.categorie, Activite.date_debut");
        $requete->execute([$idPersonnel, $dateFin, $dateDebut]);

        $activitesParCategorie = [];
        foreach ($requete->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
            $activitesParCategorie[$ligne['categorie']][] = $ligne;
        }
        return $activitesParCategorie;
    }

    // Mes participations, avec le porteur de chaque activité (en attente d'abord)
    public function participations($idPersonnel)
    {
        $requete = $this->pdo->prepare('SELECT Activite.id_activite, Activite.titre, Activite.role, Activite.confirme,
            Activite.date_debut, Activite.date_fin, TypeActivite.nom_type, Porteur.prenom, Porteur.nom
            FROM Activite
            JOIN TypeActivite ON TypeActivite.id_type = Activite.id_type
            JOIN Activite Principale ON Principale.id_activite = Activite.id_activite_principale
            JOIN Personnel Porteur ON Porteur.id = Principale.id_personnel
            WHERE Activite.id_personnel = ?
            ORDER BY Activite.confirme IS NOT NULL, Activite.date_debut DESC');
        $requete->execute([$idPersonnel]);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Réponses des participants aux activités dont je suis le porteur
    public function reponsesParticipants($idPersonnel)
    {
        $requete = $this->pdo->prepare('SELECT Activite.titre, Activite.role, Activite.confirme, Personnel.prenom, Personnel.nom
            FROM Activite
            JOIN Activite Principale ON Principale.id_activite = Activite.id_activite_principale
            JOIN Personnel ON Personnel.id = Activite.id_personnel
            WHERE Principale.id_personnel = ?
            ORDER BY Activite.titre, Personnel.nom, Personnel.prenom');
        $requete->execute([$idPersonnel]);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Enregistre la réponse : seulement sa propre ligne, et seulement si elle est en attente
    public function repondre($idActivite, $idPersonnel, $confirme)
    {
        $requete = $this->pdo->prepare('UPDATE Activite SET confirme = ?
            WHERE id_activite = ? AND id_personnel = ? AND id_activite_principale IS NOT NULL AND confirme IS NULL');
        $requete->execute([$confirme, $idActivite, $idPersonnel]);
    }

    // Invitations à des activités collectives qui attendent une réponse
    public function nombreInvitations($idPersonnel)
    {
        $requete = $this->pdo->prepare('SELECT COUNT(*) FROM Activite WHERE id_personnel = ? AND id_activite_principale IS NOT NULL AND confirme IS NULL');
        $requete->execute([$idPersonnel]);
        return $requete->fetchColumn();
    }
}
