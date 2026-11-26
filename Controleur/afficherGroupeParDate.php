<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';
require_once __DIR__ . '/../Model/Organisation.php';

$organisationModele = new Organisation($pdo);

// Période choisie (aujourd'hui par défaut)
$debut = $_GET['debut'] ?? date('Y-m-d');
$fin = $_GET['fin'] ?? date('Y-m-d');
$stmt = [];

// Si la période est correcte, on cherche les groupes ouverts sur toute la période
if ($debut <= $fin) {
    $stmt = $organisationModele->groupesSurPeriode($debut, $fin);
}

require __DIR__ . '/../Vue/afficherGroupeParDate.php';
