<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/declarerActivite.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activité</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require __DIR__ . '/navbar.php'; ?>
    
    <div class="page-contenu">
        <h1>Déclarer une activité individuelle</h1>
        <?php if ($activiteIntrouvable): ?>
            <p style="color: red">Activité introuvable.</p>
        <?php endif; ?>
        <?php if ($activiteTrouvable): ?>
            <p style="color : green">Activité trouver</p>
        <?php endif; ?>
    <form action="" method="POST">
        <label for="Type d'activité">
            <Select>
                <option value="1">activité1</option>
                <option value="2">activite2</option>
            </Select>
        </label>
    </form>