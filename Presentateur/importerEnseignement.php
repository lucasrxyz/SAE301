<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';
require_once __DIR__ . '/../Model/Enseignement.php';

// Chaque personnel importe ses propres enseignements
Droits::exiger('declarer_activite');

// Les dates du fichier sont converties à l'heure française
date_default_timezone_set('Europe/Paris');

$activiteModele = new Activite($pdo);
$enseignementModele = new Enseignement($pdo);

// Valeurs de départ de la page
$erreurICS = '';
$messageImport = '';
$evenementsApercu = [];
$idPersonnel = $_SESSION['id_personnel'];
$idTypeCours = $activiteModele->idType('Cours');

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
        $evenements = $enseignementModele->lireFichier($fichier['tmp_name']);

        if ($enseignementModele->evenementsValides($evenements)) {
            // On garde les événements en session jusqu'au clic sur Importer
            $_SESSION['import_ics'] = ['id_personnel' => $idPersonnel, 'evenements' => $evenements];
            $evenementsApercu = $enseignementModele->analyser($idTypeCours, $idPersonnel, $evenements);
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
        // On refait l'analyse au moment de l'import, au cas où la base a changé depuis l'aperçu
        $evenements = $enseignementModele->analyser($idTypeCours, $idPersonnel, $_SESSION['import_ics']['evenements']);
        $resultat = $enseignementModele->importer($idTypeCours, $idPersonnel, $evenements);

        // Oublie l'aperçu et recharge la page avec le résultat
        unset($_SESSION['import_ics']);
        header('Location: index.php?page=importerEnseignement&importe=' . $resultat['importes'] . '&modifies=' . $resultat['modifies'] . '&doublons=' . $resultat['doublons']);
        exit;
    }
}

require __DIR__ . '/../Vue/importerEnseignement.php';
