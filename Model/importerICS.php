<?php
// Connexion à la base de données et droits
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/droits.php';

// Les dates du fichier sont converties à l'heure française
date_default_timezone_set('Europe/Paris');

// Transforme une date ICS (20261014T080000) en date pour la base (2026-10-14 08:00:00)
function convertirDateICS($valeur)
{
    $horodatage = strtotime($valeur);
    if ($horodatage === false) {
        return '';
    }
    return date('Y-m-d H:i:s', $horodatage);
}

// Dans le fichier, une ligne a la forme NOM:valeur : on garde ce qui est après les deux-points
function valeurLigneICS($ligne)
{
    return trim(substr($ligne, strpos($ligne, ':') + 1));
}

// Lit le fichier et renvoie la liste de ses événements
function lireFichierICS($cheminFichier)
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
            // Début d'un événement
            $evenement = ['intitule' => '', 'date_debut' => '', 'date_fin' => ''];
        } elseif (trim($ligne) === 'END:VEVENT') {
            // Fin de l'événement : on l'ajoute à la liste
            $evenements[] = $evenement;
            $evenement = null;
        } elseif ($evenement !== null) {
            // Dans un événement, on garde le titre, le début et la fin
            if (str_starts_with($ligne, 'SUMMARY')) {
                // Dans un fichier ICS, une virgule s'écrit \, : on la remet normalement
                $evenement['intitule'] = str_replace('\\,', ',', valeurLigneICS($ligne));
            }
            if (str_starts_with($ligne, 'DTSTART')) {
                $evenement['date_debut'] = convertirDateICS(valeurLigneICS($ligne));
            }
            if (str_starts_with($ligne, 'DTEND')) {
                $evenement['date_fin'] = convertirDateICS(valeurLigneICS($ligne));
            }
        }
    }

    return $evenements;
}

// Vérifie que le fichier contient des événements, et qu'ils sont tous complets et corrects
function evenementsValides($evenements)
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

function analyserEvenements($pdo, $idTypeCours, $idPersonnel, $evenements)
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

        $requete = $pdo->prepare('SELECT id_activite, date_debut, date_fin FROM Activite WHERE id_type = ? AND id_personnel = ? AND titre = ? AND CAST(date_debut AS DATE) = ? ORDER BY date_debut');
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

// Valeurs de départ de la page
$erreurICS = '';
$messageImport = '';
$evenementsApercu = [];
$idPersonnel = $_SESSION['id_personnel'];

// Cherche le numéro du type d'activité « Cours »
$requete = $pdo->query("SELECT id_type FROM TypeActivite WHERE nom_type = 'Cours'");
$idTypeCours = $requete->fetchColumn();

// Message de résultat après un import
if (isset($_GET['importe'])) {
    $messageImport = (int) $_GET['importe'] . ' enseignement(s) ajouté(s), ' . (int) ($_GET['modifies'] ?? 0) . ' modifié(s), ' . (int) $_GET['doublons'] . ' doublon(s) ignoré(s).';
}

// Bouton Prévisualiser : on contrôle le fichier, puis on affiche l'aperçu
if (isset($_POST['previsualiser'])) {
    $fichier = $_FILES['fichier'];
    $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        $erreurICS = 'Le fichier ICS n’a pas pu être téléversé.';
    } elseif ($extension !== 'ics') {
        $erreurICS = 'Fichier ICS invalide.';
    } elseif ($fichier['size'] > 5 * 1024 * 1024) {
        $erreurICS = 'Le fichier ICS ne doit pas dépasser 5 Mo.';
    } else {
        $evenements = lireFichierICS($fichier['tmp_name']);

        if (evenementsValides($evenements)) {
            // On garde les événements en session jusqu'au clic sur Importer
            $_SESSION['import_ics'] = ['id_personnel' => $idPersonnel, 'evenements' => $evenements];
            $evenementsApercu = analyserEvenements($pdo, $idTypeCours, $idPersonnel, $evenements);
        } else {
            $erreurICS = 'Fichier ICS invalide.';
        }
    }
}

// Bouton Importer : on enregistre les événements gardés en session
if (isset($_POST['importer'])) {
    if (!isset($_SESSION['import_ics']) || $_SESSION['import_ics']['id_personnel'] !== $idPersonnel) {
        $erreurICS = 'Cet aperçu a expiré ou n’est plus disponible. Veuillez recommencer.';
    } else {
        $evenements = analyserEvenements($pdo, $idTypeCours, $idPersonnel, $_SESSION['import_ics']['evenements']);

        $nombreImportes = 0;
        $nombreModifies = 0;
        $nombreDoublons = 0;

        // Si une écriture plante, on annule tout
        $pdo->beginTransaction();

        foreach ($evenements as $evenement) {
            if ($evenement['statut'] === 'Nouveau') {
                $requete = $pdo->prepare('INSERT INTO Activite (id_type, id_personnel, titre, date_debut, date_fin) VALUES (?, ?, ?, ?, ?)');
                $requete->execute([$idTypeCours, $idPersonnel, $evenement['intitule'], $evenement['date_debut'], $evenement['date_fin']]);
                $nombreImportes++;
            } elseif ($evenement['statut'] === 'Modifié') {
                $requete = $pdo->prepare('UPDATE Activite SET date_debut = ?, date_fin = ? WHERE id_activite = ? AND id_personnel = ?');
                $requete->execute([$evenement['date_debut'], $evenement['date_fin'], $evenement['id_activite'], $idPersonnel]);
                $nombreModifies++;
            } else {
                $nombreDoublons++;
            }
        }

        $pdo->commit();

        // Oublie l'aperçu et recharge la page avec le résultat
        unset($_SESSION['import_ics']);
        header('Location: importerEnseignement.php?importe=' . $nombreImportes . '&modifies=' . $nombreModifies . '&doublons=' . $nombreDoublons);
        exit;
    }
}
