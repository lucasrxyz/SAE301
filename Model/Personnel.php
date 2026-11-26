<?php
// Requêtes sur les personnels, leurs statuts et leurs noms
class Personnel
{
    // Liste des statuts qu'un personnel peut avoir
    const STATUTS = [
        'Doctorant',
        'Post-doctorant',
        'Maître de conférences',
        'Professeur des universités',
        'Chargé de recherche',
        'Directeur de recherche',
        'Ingénieur d\'études',
        'Ingénieur de recherche'
    ];

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Prénom et nom, pour les afficher
    public function prenomEtNom($idPersonnel)
    {
        $requete = $this->pdo->prepare('SELECT prenom, nom FROM Personnel WHERE id = :id');
        $requete->execute(['id' => $idPersonnel]);
        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    // Cherche un personnel par son numéro
    public function trouver($idPersonnel)
    {
        $requete = $this->pdo->prepare('SELECT id, nom, prenom, mail FROM Personnel WHERE id = ?');
        $requete->execute([$idPersonnel]);
        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    // Toutes les informations d'identité, pour le rapport
    public function identite($idPersonnel)
    {
        $requete = $this->pdo->prepare(
            "SELECT id, nom, prenom, mail, id_orcid, id_idhal, date_arrivee, date_depart
            FROM Personnel WHERE id = ?");
        $requete->execute([$idPersonnel]);
        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    // Liste du personnel dans le périmètre : tout, seulement les rattachés, ou une recherche
    public function lister($perimetre, $choix, $recherche)
    {
        [$filtrePerimetre, $parametres] = Droits::conditionPerimetre($perimetre);

        // Début de la requête, commun aux trois recherches
        $texteRequete = "SELECT DISTINCT Personnel.*, Rattachement.id IS NULL AS sans_rattachement
            FROM Personnel
            LEFT JOIN Rattachement ON Rattachement.id_personnel = Personnel.id
            LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
            LEFT JOIN Personnel_Nom_Historique ON Personnel_Nom_Historique.id_personnel = Personnel.id
            WHERE TRUE" . $filtrePerimetre;

        if ($choix === 'rattaches') {
            $texteRequete .= " AND Rattachement.id IS NOT NULL";
        } elseif ($choix === 'recherche') {
            // Recherche par nom, prénom, ancien nom, ORCID ou IdHAL
            $texteRequete .= "
                AND (Personnel.nom ILIKE :nom
                OR Personnel.prenom ILIKE :nom
                OR Personnel_Nom_Historique.ancien_nom ILIKE :nom
                OR Personnel.id_orcid = :exact
                OR Personnel.id_idhal = :exact)";
            $parametres['nom'] = '%' . $recherche . '%';
            $parametres['exact'] = $recherche;
        }

        $requete = $this->pdo->prepare($texteRequete . " ORDER BY Personnel.nom, Personnel.prenom");
        $requete->execute($parametres);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Les personnes qui ont le même nom et prénom (homonymes)
    public function homonymes()
    {
        $requete = $this->pdo->query("SELECT nom, prenom FROM Personnel GROUP BY nom, prenom HAVING COUNT(*) > 1");
        $homonymes = [];
        foreach ($requete->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
            $homonymes[] = $ligne['nom'] . ' ' . $ligne['prenom'];
        }
        return $homonymes;
    }

    // Cherche un personnel, seulement s'il est dans le périmètre autorisé
    public function trouverDansPerimetre($idPersonnel, $perimetre)
    {
        [$filtrePerimetre, $parametres] = Droits::conditionPerimetre($perimetre);
        $parametres['id'] = $idPersonnel;

        $requete = $this->pdo->prepare("SELECT DISTINCT Personnel.*
            FROM Personnel
            LEFT JOIN Rattachement ON Rattachement.id_personnel = Personnel.id
            LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
            WHERE Personnel.id = :id" . $filtrePerimetre);
        $requete->execute($parametres);
        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    public function historiqueStatuts($idPersonnel)
    {
        $requete = $this->pdo->prepare("SELECT statut, quotite_recherche, date_debut, date_fin FROM Personnel_Statut_Historique WHERE id_personnel = :id ORDER BY date_debut DESC");
        $requete->execute(['id' => $idPersonnel]);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Statuts occupés pendant une période, pour le rapport
    public function statutsSurPeriode($idPersonnel, $dateDebut, $dateFin)
    {
        $requete = $this->pdo->prepare(
            "SELECT statut, quotite_recherche, date_debut, date_fin
            FROM Personnel_Statut_Historique
            WHERE id_personnel = ?
            AND date_debut <= ?
            AND (date_fin IS NULL OR date_fin >= ?)
            ORDER BY date_debut");
        $requete->execute([$idPersonnel, $dateFin, $dateDebut]);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    public function anciensNoms($idPersonnel)
    {
        $requete = $this->pdo->prepare("SELECT ancien_nom, date_changement FROM Personnel_Nom_Historique WHERE id_personnel = :id ORDER BY date_changement DESC");
        $requete->execute(['id' => $idPersonnel]);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Vrai si ce mail est déjà utilisé par un autre personnel, sans tenir compte des majuscules
    public function mailUtilise($mail, $idPersonnelExclu)
    {
        $requete = $this->pdo->prepare('SELECT COUNT(*) FROM Personnel WHERE LOWER(mail) = LOWER(?) AND id <> ?');
        $requete->execute([$mail, $idPersonnelExclu]);
        return $requete->fetchColumn() > 0;
    }

    // Crée le personnel avec son premier statut, et renvoie son numéro
    public function ajouter($nom, $prenom, $mail, $dateArrivee, $statut, $quotite)
    {
        // Les deux ajouts réussissent ensemble ou pas du tout
        $this->pdo->beginTransaction();

        $requete = $this->pdo->prepare('INSERT INTO Personnel (nom, prenom, mail, date_arrivee) VALUES (?, ?, ?, ?) RETURNING id');
        $requete->execute([$nom, $prenom, $mail, $dateArrivee]);
        $idPersonnel = $requete->fetchColumn();

        // La quotité passe de 80 à 0.8
        $requete = $this->pdo->prepare('INSERT INTO Personnel_Statut_Historique (id_personnel, statut, quotite_recherche, date_debut) VALUES (?, ?, ?, ?)');
        $requete->execute([$idPersonnel, $statut, $quotite / 100, $dateArrivee]);

        $this->pdo->commit();
        return $idPersonnel;
    }

    // Modifie les coordonnées ; si le nom change, l'ancien est gardé dans l'historique
    public function modifier($idPersonnel, $ancienNom, $nom, $prenom, $mail)
    {
        $this->pdo->beginTransaction();

        if ($nom !== $ancienNom) {
            $requete = $this->pdo->prepare('INSERT INTO Personnel_Nom_Historique (id_personnel, ancien_nom, date_changement) VALUES (?, ?, CURRENT_DATE)');
            $requete->execute([$idPersonnel, $ancienNom]);
        }

        $requete = $this->pdo->prepare('UPDATE Personnel SET nom = ?, prenom = ?, mail = ? WHERE id = ?');
        $requete->execute([$nom, $prenom, $mail, $idPersonnel]);

        $this->pdo->commit();
    }

    // Autre personne qui a déjà cet ORCID
    public function autreAvecOrcid($orcid, $idPersonnel)
    {
        $requete = $this->pdo->prepare('SELECT nom, prenom FROM Personnel WHERE id_orcid = ? AND id <> ?');
        $requete->execute([$orcid, $idPersonnel]);
        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    // Autre personne qui a déjà cet IdHAL
    public function autreAvecIdhal($idhal, $idPersonnel)
    {
        $requete = $this->pdo->prepare('SELECT nom, prenom FROM Personnel WHERE id_idhal = ? AND id <> ?');
        $requete->execute([$idhal, $idPersonnel]);
        return $requete->fetch(PDO::FETCH_ASSOC);
    }

    // Enregistre l'ORCID et l'IdHAL (un champ vide devient NULL)
    public function modifierIdentifiants($idPersonnel, $orcid, $idhal)
    {
        $requete = $this->pdo->prepare('UPDATE Personnel SET id_orcid = ?, id_idhal = ? WHERE id = ?');
        $requete->execute([$orcid === '' ? null : $orcid, $idhal === '' ? null : $idhal, $idPersonnel]);
    }

    public function nomActuel($idPersonnel)
    {
        $requete = $this->pdo->prepare('SELECT nom FROM Personnel WHERE id = ?');
        $requete->execute([$idPersonnel]);
        return $requete->fetchColumn();
    }

    // Change le nom en gardant l'ancien dans l'historique
    public function changerNom($idPersonnel, $ancienNom, $nouveauNom, $dateChangement)
    {
        $this->pdo->beginTransaction();

        $requete = $this->pdo->prepare('INSERT INTO Personnel_Nom_Historique (id_personnel, ancien_nom, date_changement) VALUES (?, ?, ?)');
        $requete->execute([$idPersonnel, $ancienNom, $dateChangement]);

        $requete = $this->pdo->prepare('UPDATE Personnel SET nom = ? WHERE id = ?');
        $requete->execute([$nouveauNom, $idPersonnel]);

        $this->pdo->commit();
    }

    // Date de début du statut actuel (celui qui n'a pas de date de fin)
    public function debutStatutActuel($idPersonnel)
    {
        $requete = $this->pdo->prepare('SELECT date_debut FROM Personnel_Statut_Historique WHERE id_personnel = ? AND date_fin IS NULL');
        $requete->execute([$idPersonnel]);
        return $requete->fetchColumn();
    }

    // Ferme l'ancien statut la veille et ajoute le nouveau
    public function changerStatut($idPersonnel, $nouveauStatut, $nouvelleQuotite, $dateEffet)
    {
        $this->pdo->beginTransaction();

        $requete = $this->pdo->prepare("UPDATE Personnel_Statut_Historique SET date_fin = ?::date - 1 WHERE id_personnel = ? AND date_fin IS NULL");
        $requete->execute([$dateEffet, $idPersonnel]);

        $requete = $this->pdo->prepare('INSERT INTO Personnel_Statut_Historique (id_personnel, statut, quotite_recherche, date_debut) VALUES (?, ?, ?, ?)');
        $requete->execute([$idPersonnel, $nouveauStatut, $nouvelleQuotite / 100, $dateEffet]);

        $this->pdo->commit();
    }

    // Date avant laquelle la personne ne peut pas partir : la plus récente de ses dates
    public function dateMinimumDepart($idPersonnel)
    {
        $requete = $this->pdo->prepare('SELECT date_arrivee FROM Personnel WHERE id = ?');
        $requete->execute([$idPersonnel]);
        $dateArrivee = $requete->fetchColumn();

        $requete = $this->pdo->prepare('SELECT MAX(date_debut) FROM Personnel_Statut_Historique WHERE id_personnel = ? AND date_fin IS NULL');
        $requete->execute([$idPersonnel]);
        $debutStatut = $requete->fetchColumn();

        $requete = $this->pdo->prepare('SELECT MAX(date_debut) FROM Rattachement WHERE id_personnel = ? AND date_fin IS NULL');
        $requete->execute([$idPersonnel]);
        $debutRattachement = $requete->fetchColumn();

        $requete = $this->pdo->prepare('SELECT date_activation FROM Compte WHERE id_personnel = ?');
        $requete->execute([$idPersonnel]);
        $activationCompte = $requete->fetchColumn();

        return max($dateArrivee, $debutStatut, $debutRattachement, $activationCompte);
    }

    // On ne supprime jamais : on marque la personne comme partie et on ferme tout ce qui est en cours
    public function desactiver($idPersonnel, $dateDepart)
    {
        $this->pdo->beginTransaction();

        $requete = $this->pdo->prepare('UPDATE Personnel SET actif = false, date_depart = ? WHERE id = ?');
        $requete->execute([$dateDepart, $idPersonnel]);

        $requete = $this->pdo->prepare('UPDATE Compte SET actif = false, date_desactivation = ? WHERE id_personnel = ?');
        $requete->execute([$dateDepart, $idPersonnel]);

        $requete = $this->pdo->prepare('UPDATE Personnel_Statut_Historique SET date_fin = ? WHERE id_personnel = ? AND date_fin IS NULL');
        $requete->execute([$dateDepart, $idPersonnel]);

        $requete = $this->pdo->prepare('UPDATE Rattachement SET date_fin = ? WHERE id_personnel = ? AND date_fin IS NULL');
        $requete->execute([$dateDepart, $idPersonnel]);

        $this->pdo->commit();
    }

    // Collaborateurs possibles : personnels actifs avec IdHAL et ORCID, sauf moi
    public function collaborateursPossibles($idPersonnel)
    {
        $requete = $this->pdo->prepare('SELECT id, nom, prenom, id_idhal, id_orcid FROM Personnel WHERE actif = TRUE AND id_idhal IS NOT NULL AND id_orcid IS NOT NULL AND id <> ? ORDER BY nom, prenom');
        $requete->execute([$idPersonnel]);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Collaborateurs qui correspondent aux filtres, d'après leur rattachement en cours
    public function collaborateursFiltres($idPersonnel, $filtreNom, $filtreDepartement, $filtreGroupe)
    {
        $texteRequete = 'SELECT DISTINCT Personnel.id, Personnel.nom, Personnel.prenom, Personnel.id_idhal, Personnel.id_orcid
            FROM Personnel
            LEFT JOIN Rattachement ON Rattachement.id_personnel = Personnel.id AND Rattachement.date_fin IS NULL
            LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
            WHERE Personnel.actif = TRUE AND Personnel.id_idhal IS NOT NULL AND Personnel.id_orcid IS NOT NULL AND Personnel.id <> :id';
        $parametres = ['id' => $idPersonnel];

        if ($filtreNom !== '') {
            $texteRequete .= ' AND (Personnel.nom ILIKE :nom OR Personnel.prenom ILIKE :nom)';
            $parametres['nom'] = '%' . $filtreNom . '%';
        }
        if ($filtreDepartement !== '') {
            $texteRequete .= ' AND (Rattachement.id_departement = :departement OR Groupe.id_departement = :departement)';
            $parametres['departement'] = (int) $filtreDepartement;
        }
        if ($filtreGroupe !== '') {
            $texteRequete .= ' AND Rattachement.id_groupe = :groupe';
            $parametres['groupe'] = (int) $filtreGroupe;
        }

        $requete = $this->pdo->prepare($texteRequete . ' ORDER BY Personnel.nom, Personnel.prenom');
        $requete->execute($parametres);
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    // Personnels qui ont un IdHAL
    public function avecIdhal()
    {
        $requete = $this->pdo->query("SELECT id, nom, prenom, id_idhal FROM Personnel WHERE id_idhal IS NOT NULL AND id_idhal <> '' ORDER BY nom, prenom");
        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }
}
