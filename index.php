<?php
    session_start();
    require __DIR__ . '/../Model/droits.php';

    if (empty($_SESSION['identifiant'])) {
        header('Location: connexion.php');
    } else {
        header('Location: ' . pageAccueil());
    }
    exit;