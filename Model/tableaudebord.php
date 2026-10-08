<?php
    require_once __DIR__ . '/config.php';

    $perimetre = $_GET['perimetre'] ?? 'laboratoire';
    $idChoisi = $_GET['id'] ?? '';
    $dateDebut = $_GET['debut'] ?? date('Y') . '-01-01';
    $dateFin = $_GET['fin'] ?? date('Y') . '-12-31';
    $listeDepartements = $pdo->prepare("SELECT id_departement, nom FROM Departement");
    $listeDepartements->execute();
    