<?php
    // Vérifie que l'utilisateur est connecté
    require __DIR__ . '/../Model/verifierconnexion.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php // Barre de navigation, commune à toutes les pages ?>
    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="page-contenu formulaire-page">
        <section class="formulaire-carte">
            <p class="formulaire-surtitre">Droits insuffisants</p>
            <?php // Page affichée quand on n'a pas le droit d'ouvrir une page ?>
            <h1>Accès refusé</h1>
            <?php if (empty($_SESSION['roles'])): ?>
                <p>Aucun rôle n'est attribué à votre compte. Contactez l'administrateur fonctionnel.</p>
            <?php else: ?>
                <p>Votre rôle ne vous permet pas d'accéder à cette page.</p>
                <p><a href="<?= pageAccueil() ?>">Retour à l'accueil</a></p>
            <?php endif; ?>
        </section>
    </main>

</body>
</html>