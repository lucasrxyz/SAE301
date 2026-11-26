<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';
require_once __DIR__ . '/../Model/Organisation.php';

// Vérifie le droit de voir l'organisation
Droits::exiger('voir_organisation');

$organisationModele = new Organisation($pdo);

// Vue choisie et date consultée (aujourd'hui par défaut)
$vue = $_GET['vue'] ?? null;
$dateConsultee = $_GET['date'] ?? date('Y-m-d');

// Si la date n'est pas valide, on prend aujourd'hui
$date = DateTime::createFromFormat('!Y-m-d', $dateConsultee);
if (!$date || $date->format('Y-m-d') !== $dateConsultee) {
    $dateConsultee = date('Y-m-d');
}

// Organisation à la date choisie, selon la vue (vide si aucune vue choisie)
$stmt = $organisationModele->aLaDate($vue, $dateConsultee);

require __DIR__ . '/../Vue/organisation.php';
