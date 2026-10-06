<?php
    require 'config.php';

    $resultats = [];
    $aucunResultat = false;
    $nomutil = '';
    $parametreRetour = '';

    if (isset($_GET['tout'])) {
        $stmt = $pdo->prepare("SELECT * FROM Personnel");
        $stmt->execute();
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$resultats) {
            $aucunResultat = true;
        }

        $parametreRetour = 'tout=1';
    }
    elseif (isset($_GET['nom']) && $_GET['nom'] !== '') {
        $nomutil = $_GET['nom'];

        $stmt = $pdo->prepare("SELECT * FROM Personnel WHERE UPPER(nom) = UPPER(?)");
        $stmt->execute([$nomutil]);
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$resultats) {
            $aucunResultat = true;
        }

        $parametreRetour = 'nom=' . urlencode($nomutil);
    }

    $ligneChoisie = null;
    if (isset($_GET['voir'])) {
        $indexChoisi = (int) $_GET['voir'];
        if (isset($resultats[$indexChoisi])) {
            $ligneChoisie = $resultats[$indexChoisi];
        }
    }
?>