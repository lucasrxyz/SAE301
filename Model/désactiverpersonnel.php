<?php
require_once __DIR__ . '/config.php';
$erreurDesactivation = '';
$personnelDesactive = isset($_GET['desactive']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPersonnel = $_POST['id_personnel'] ?? null;
    if ($_POST['desactiver'] === 'true') {
        $datedepart = $_POST['date_depart'] ?? null;
        if ($datedepart) {
            $requete = $pdo->prepare('UPDATE Personnel SET actif = false, date_depart = ? WHERE id = ?');
            $requete->execute([$datedepart, $idPersonnel]);
            header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&desactive=1');
            exit;
        } else {
            $erreurDesactivation = 'Veuillez saisir une date de départ.';
        }
    }
}