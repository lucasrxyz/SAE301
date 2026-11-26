<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';
require_once __DIR__ . '/../Model/Personnel.php';
require_once __DIR__ . '/../Model/Publication.php';

// Seuls le RH et l'administrateur peuvent importer
Droits::exiger('gerer_personnel');

$personnelModele = new Personnel($pdo);
$publicationModele = new Publication($pdo);

// Valeurs de départ
$erreurImport = '';
$nbImportees = isset($_GET['importees']) ? (int) $_GET['importees'] : -1;
$idPersonnel = (int) ($_POST['id_personnel'] ?? 0);
$publications = [];
$nbNouvelles = 0;
$nbAutresTypes = 0;
$apercuAffiche = false;

// Personnels qui ont un IdHAL, pour la liste
$personnels = $personnelModele->avecIdhal();

// S'exécute quand on clique sur Aperçu ou Importer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['apercu']) || isset($_POST['importer']))) {
    // Retrouve l'IdHAL du personnel choisi
    $idHal = '';
    foreach ($personnels as $personnel) {
        if ($personnel['id'] == $idPersonnel) {
            $idHal = $personnel['id_idhal'];
        }
    }

    // Le personnel choisi doit avoir un IdHAL
    if ($idHal === '') {
        $erreurImport = 'Veuillez choisir un personnel qui a un IdHAL.';
    } else {
        $xml = $publicationModele->chercherSurHal($idHal);

        // Pas de réponse lisible de HAL
        if ($xml === false) {
            $erreurImport = 'HAL ne répond pas pour le moment. Réessayez plus tard.';
        } else {
            $apercuAffiche = true;
            [$publications, $nbNouvelles, $nbAutresTypes] = $publicationModele->lireReponse($xml);

            // Bouton Importer : on enregistre les nouvelles publications
            if (isset($_POST['importer'])) {
                $nbImportees = $publicationModele->importer($publications, $idPersonnel);

                // Recharge la page avec le nombre d'imports
                header('Location: importerPublications.php?importees=' . $nbImportees);
                exit;
            }
        }
    }
}

require __DIR__ . '/../Vue/importerPublications.php';
