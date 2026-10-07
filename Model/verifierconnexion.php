<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['identifiant'])) {
    header('Location: connexion.php');
    exit;
}