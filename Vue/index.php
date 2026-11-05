<?php
    // Page d'arrivée du site : on ouvre la session et on charge les droits
    session_start();
    require __DIR__ . '/../Model/droits.php';

    // Pas connecté : vers la connexion, sinon vers sa page d'accueil
    if (empty($_SESSION['identifiant'])) {
        header('Location: connexion.php');
    } else {
        header('Location: ' . pageAccueil());
    }
    exit;