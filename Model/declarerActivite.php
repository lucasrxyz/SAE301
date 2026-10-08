<?php
require_once __DIR__ . '/config.php';

$erreurActivite = '';
$activiteEnregistree = isset($_GET['enregistree']);
$idPersonnel = $_SESSION['id_personnel'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $typeactivite = $_POST['id_type'] ?? '';
    $titre = trim($_POST['titre'] ?? '');
    $datedebut = $_POST['date_debut'] ?? '';
    $datefin = $_POST['date_fin'] ?? '';

    if ($titre === '' || $datedebut === '') {
        $erreurActivite = 'Le titre et la date de début sont obligatoires.';
    } elseif ($datefin !== '' && $datefin < $datedebut) {
        $erreurActivite = 'La date de fin doit être après la date de début.';
    } else {
        if ($datefin === '') {
            $datefin = null;
        }

        $stmt = $pdo->prepare('INSERT INTO Activite (id_type, id_personnel, titre, date_debut, date_fin) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$typeactivite, $idPersonnel, $titre, $datedebut, $datefin]);

        header('Location: declarerActivite.php?enregistree=1');
        exit;
    }
}