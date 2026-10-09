<?php
    session_start();
    if (empty($_SESSION['identifiant'])) {
        header('Location: connexion.php');
    } else {
        header('Location: tableauDeBord.php');
    }
    exit;