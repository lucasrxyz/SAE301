<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';
require_once __DIR__ . '/../Model/Organisation.php';
require_once __DIR__ . '/../Model/TableauDeBord.php';

Droits::exiger('voir_tableau_bord');

$organisationModele = new Organisation($pdo);

// Périmètre et période choisis (l'année en cours par défaut)
$choix = $_GET['choix'] ?? '';
$dateDebut = $_GET['debut'] ?? date('Y') . '-01-01';
$dateFin = $_GET['fin'] ?? date('Y') . '-12-31';

// Le choix vaut par exemple 'groupe-2' : on le coupe en deux
$perimetre = 'laboratoire';
$idChoisi = '';
if ($choix !== '') {
    [$perimetre, $idChoisi] = explode('-', $choix);
}

// Si la personne n'a pas accès à tout le labo, on lui impose son périmètre
$perimetreAutorise = Droits::perimetre($pdo);
$perimetreImpose = $perimetreAutorise['type'] !== 'laboratoire';
if ($perimetreImpose) {
    $perimetre = $perimetreAutorise['type'];
    $idChoisi = $perimetreAutorise['id'];
}

// Listes pour le formulaire
$departements = $organisationModele->departements();
$groupes = $organisationModele->groupes();

// Chiffres du périmètre sur la période
$tableauDeBord = new TableauDeBord($pdo, $perimetre, $idChoisi, $dateDebut, $dateFin);
$personnes = $tableauDeBord->personnes();
$effectifsParStatut = $tableauDeBord->quotitesParStatut();
$activitesParCategorie = $tableauDeBord->activitesParCategorie();
$totalActivites = array_sum(array_column($activitesParCategorie, 'nombre'));

// Compte les personnes, les arrivées et les départs sur la période
$effectifTotal = count($personnes);
$nombreEntrees = 0;
$nombreSorties = 0;
foreach ($personnes as $personne) {
    if ($personne['date_arrivee'] >= $dateDebut && $personne['date_arrivee'] <= $dateFin) {
        $nombreEntrees++;
    }
    if ($personne['date_depart'] !== null && $personne['date_depart'] >= $dateDebut && $personne['date_depart'] <= $dateFin) {
        $nombreSorties++;
    }
}

// Nom du périmètre à afficher en haut de la page
$nomPerimetre = 'Tout le laboratoire';
if ($perimetre === 'personnel') {
    $nomPerimetre = 'Mes données';
}
foreach ($departements as $departement) {
    if ($perimetre === 'departement' && $idChoisi == $departement['id_departement']) {
        $nomPerimetre = 'Département ' . $departement['nom'];
    }
}
foreach ($groupes as $groupe) {
    if ($perimetre === 'groupe' && $idChoisi == $groupe['id_groupe']) {
        $nomPerimetre = 'Groupe ' . $groupe['nom'];
    }
}

require __DIR__ . '/../Vue/tableauDeBord.php';
