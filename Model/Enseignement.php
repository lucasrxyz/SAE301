<?php
// Lecture d'un fichier ICS et enregistrement des cours (activités de type Cours)
class Enseignement
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Transforme une date ICS (20261014T080000) en date pour la base (2026-10-14 08:00:00)
    private function convertirDate($valeur)
    {
        $horodatage = strtotime($valeur);
        if ($horodatage === false) {
            return '';
        }
        return date('Y-m-d H:i:s', $horodatage);
    }

    // Dans le fichier, une ligne a la forme NOM:valeur : on garde ce qui est après les deux-points
    private function valeurLigne($ligne)
    {
        return trim(substr($ligne, strpos($ligne, ':') + 1));
    }

    // Lit le fichier et renvoie la liste de ses événements
    public function lireFichier($cheminFichier)
    {
        $lignesDuFichier = file($cheminFichier, FILE_IGNORE_NEW_LINES);

        // Une ligne trop longue continue sur la suivante, qui commence par un espace : on les recolle
        $lignes = [];
        foreach ($lignesDuFichier as $ligne) {
            if (str_starts_with($ligne, ' ') && count($lignes) > 0) {
                $lignes[count($lignes) - 1] .= substr($ligne, 1);
            } else {
                $lignes[] = $ligne;
            }
        }

        $evenements = [];
        $evenement = null;

        foreach ($lignes as $ligne) {
            if (trim($ligne) === 'BEGIN:VEVENT') {
                $evenement = ['intitule' => '', 'date_debut' => '', 'date_fin' => ''];
            } elseif (trim($ligne) === 'END:VEVENT') {
                $evenements[] = $evenement;
                $evenement = null;
            } elseif ($evenement !== null) {
                // Dans un événement, on garde le titre, le début et la fin
                if (str_starts_with($ligne, 'SUMMARY')) {
                    // Dans un fichier ICS, une virgule s'écrit \, : on la remet normalement
                    $evenement['intitule'] = str_replace('\\,', ',', $this->valeurLigne($ligne));
                }
                if (str_starts_with($ligne, 'DTSTART')) {
                    $evenement['date_debut'] = $this->convertirDate($this->valeurLigne($ligne));
                }
                if (str_starts_with($ligne, 'DTEND')) {
                    $evenement['date_fin'] = $this->convertirDate($this->valeurLigne($ligne));
                }
            }
        }

        return $evenements;
    }

    // Vérifie que le fichier contient des événements, et qu'ils sont tous complets et corrects
    public function evenementsValides($evenements)
    {
        if (count($evenements) === 0) {
            return false;
        }

        foreach ($evenements as $evenement) {
            if ($evenement['intitule'] === '' || strlen($evenement['intitule']) > 255) {
                return false;
            }
            if ($evenement['date_debut'] === '' || $evenement['date_fin'] === '') {
                return false;
            }
            if ($evenement['date_fin'] < $evenement['date_debut']) {
                return false;
            }
        }

        return true;
    }

    // Donne à chaque événement un statut : Nouveau, Modifié (même cours le même jour à d'autres heures) ou Doublon
    public function analyser($idTypeCours, $idPersonnel, $evenements)
    {
        $clesFichier = [];
        foreach ($evenements as $evenement) {
            $clesFichier[] = $evenement['intitule'] . '|' . $evenement['date_debut'] . '|' . $evenement['date_fin'];
        }

        $clesVues = [];
        $idsUtilises = [];
        $evenementsAnalyses = [];

        foreach ($evenements as $evenement) {
            $cle = $evenement['intitule'] . '|' . $evenement['date_debut'] . '|' . $evenement['date_fin'];
            $evenement['statut'] = 'Nouveau';
            $evenement['id_activite'] = null;

            $requete = $this->pdo->prepare('SELECT id_activite, date_debut, date_fin FROM Activite WHERE id_type = ? AND id_personnel = ? AND titre = ? AND CAST(date_debut AS DATE) = ? ORDER BY date_debut');
            $requete->execute([$idTypeCours, $idPersonnel, $evenement['intitule'], substr($evenement['date_debut'], 0, 10)]);
            $coursDuJour = $requete->fetchAll(PDO::FETCH_ASSOC);

            if (in_array($cle, $clesVues)) {
                $evenement['statut'] = 'Doublon';
            }

            foreach ($coursDuJour as $cours) {
                if ($cours['date_debut'] === $evenement['date_debut'] && $cours['date_fin'] === $evenement['date_fin']) {
                    $evenement['statut'] = 'Doublon';
                }
            }

            if ($evenement['statut'] === 'Nouveau') {
                foreach ($coursDuJour as $cours) {
                    $cleCours = $evenement['intitule'] . '|' . $cours['date_debut'] . '|' . $cours['date_fin'];
                    if ($evenement['id_activite'] === null && !in_array($cleCours, $clesFichier) && !in_array($cours['id_activite'], $idsUtilises)) {
                        $evenement['statut'] = 'Modifié';
                        $evenement['id_activite'] = $cours['id_activite'];
                        $idsUtilises[] = $cours['id_activite'];
                    }
                }
            }

            $clesVues[] = $cle;
            $evenementsAnalyses[] = $evenement;
        }

        return $evenementsAnalyses;
    }

    // Enregistre les événements analysés et renvoie le nombre d'ajouts, de modifications et de doublons
    public function importer($idTypeCours, $idPersonnel, $evenements)
    {
        $resultat = ['importes' => 0, 'modifies' => 0, 'doublons' => 0];

        // Si une écriture plante, on annule tout
        $this->pdo->beginTransaction();

        foreach ($evenements as $evenement) {
            if ($evenement['statut'] === 'Nouveau') {
                $requete = $this->pdo->prepare('INSERT INTO Activite (id_type, id_personnel, titre, date_debut, date_fin) VALUES (?, ?, ?, ?, ?)');
                $requete->execute([$idTypeCours, $idPersonnel, $evenement['intitule'], $evenement['date_debut'], $evenement['date_fin']]);
                $resultat['importes']++;
            } elseif ($evenement['statut'] === 'Modifié') {
                $requete = $this->pdo->prepare('UPDATE Activite SET date_debut = ?, date_fin = ? WHERE id_activite = ? AND id_personnel = ?');
                $requete->execute([$evenement['date_debut'], $evenement['date_fin'], $evenement['id_activite'], $idPersonnel]);
                $resultat['modifies']++;
            } else {
                $resultat['doublons']++;
            }
        }

        $this->pdo->commit();
        return $resultat;
    }
}
