<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';
require_once __DIR__ . '/../Model/Personnel.php';

// Il faut le droit de voir le personnel
Droits::exiger('voir_personnel');

$personnelModele = new Personnel($pdo);

// Ce que l'utilisateur a demandé : un nom, tout le monde, ou seulement les rattachés
$nomUtilisateur = trim($_GET['nom'] ?? '');
$voirTout = isset($_GET['tout']);
$voirRattaches = isset($_GET['rattaches']);

// Résultats vides au départ
$resultats = [];
$aucunResultat = false;

// Limite la recherche à ce que la personne a le droit de voir
$perimetreAutorise = Droits::perimetre($pdo);

if ($voirTout) {
    $resultats = $personnelModele->lister($perimetreAutorise, 'tout', '');
} elseif ($voirRattaches) {
    $resultats = $personnelModele->lister($perimetreAutorise, 'rattaches', '');
} elseif ($nomUtilisateur !== '') {
    $resultats = $personnelModele->lister($perimetreAutorise, 'recherche', $nomUtilisateur);

    // Rien trouvé : la vue affichera un message
    if (count($resultats) === 0) {
        $aucunResultat = true;
    }
}

$homonymes = $personnelModele->homonymes();

require __DIR__ . '/../Vue/afficherListePersonnel.php';
