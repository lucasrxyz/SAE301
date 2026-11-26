<?php
// Requêtes du tableau de bord : effectifs, statuts et activités d'un périmètre sur une période
class TableauDeBord
{
    private $pdo;
    private $filtre;
    private $parametres;

    // Prépare le filtre commun : rattachements en cours sur la période, dans le périmètre choisi
    public function __construct($pdo, $perimetre, $idChoisi, $dateDebut, $dateFin)
    {
        $this->pdo = $pdo;
        $this->filtre = " AND Rattachement.date_debut <= :fin AND (Rattachement.date_fin >= :debut OR Rattachement.date_fin IS NULL)";
        $this->parametres = ['debut' => $dateDebut, 'fin' => $dateFin];

        if ($perimetre === 'departement') {
            $this->filtre .= " AND (Rattachement.id_departement = :id OR Groupe.id_departement = :id)";
            $this->parametres['id'] = $idChoisi;
        } elseif ($perimetre === 'groupe') {
            $this->filtre .= " AND Rattachement.id_groupe = :id";
            $this->parametres['id'] = $idChoisi;
        } elseif ($perimetre === 'personnel') {
            $this->filtre .= " AND Rattachement.id_personnel = :id";
            $this->parametres['id'] = $idChoisi;
        }
    }

    // Personnes du périmètre
    public function personnes()
    {
        $requete = $this->pdo->prepare("SELECT DISTINCT Personnel.id, Personnel.nom, Personnel.date_arrivee, Personnel.date_depart
            FROM Rattachement
            JOIN Personnel ON Personnel.id = Rattachement.id_personnel
            LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
            WHERE TRUE" . $this->filtre);
        $requete->execute($this->parametres);
        return $requete->fetchAll();
    }

    // Quotités rangées par statut
    public function quotitesParStatut()
    {
        $requete = $this->pdo->prepare("SELECT DISTINCT Personnel_Statut_Historique.id_personnel, Personnel_Statut_Historique.statut, Personnel_Statut_Historique.quotite_recherche
            FROM Personnel_Statut_Historique
            JOIN Rattachement ON Rattachement.id_personnel = Personnel_Statut_Historique.id_personnel
            LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
            WHERE Personnel_Statut_Historique.date_debut <= :fin
            AND (Personnel_Statut_Historique.date_fin >= :debut OR Personnel_Statut_Historique.date_fin IS NULL)" . $this->filtre);
        $requete->execute($this->parametres);
        $effectifsParStatut = [];
        foreach ($requete->fetchAll() as $ligne) {
            $effectifsParStatut[$ligne['statut']][] = $ligne['quotite_recherche'];
        }
        return $effectifsParStatut;
    }

    // Activités par catégorie : une activité partagée compte une fois, et une participation seulement si elle est confirmée
    public function activitesParCategorie()
    {
        $requete = $this->pdo->prepare("SELECT TypeActivite.categorie, COUNT(DISTINCT COALESCE(Activite.id_activite_principale, Activite.id_activite)) AS nombre
            FROM Activite
            JOIN TypeActivite ON TypeActivite.id_type = Activite.id_type
            JOIN Rattachement ON Rattachement.id_personnel = Activite.id_personnel
            LEFT JOIN Groupe ON Groupe.id_groupe = Rattachement.id_groupe
            WHERE (Activite.id_activite_principale IS NULL OR Activite.confirme = TRUE)
            AND Activite.date_debut::date <= :fin
            AND (Activite.date_fin >= :debut OR Activite.date_fin IS NULL)" . $this->filtre . "
            GROUP BY TypeActivite.categorie");
        $requete->execute($this->parametres);
        return $requete->fetchAll();
    }
}
