<?php
    require __DIR__ . '/../Model/optionconnection.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | K.A.B.L Solution</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-logo">
            K.A.B.L Solution <span>Hauts-de-France</span>
        </div>
    </nav>

    <main class="page-contenu connexion-page">
        <section class="connexion-panel" aria-labelledby="titre-connexion">
            <p class="connexion-surtitre">Espace personnel</p>
            <h1 id="titre-connexion">Connexion</h1>
            <p>Identifiez-vous pour accéder à la liste du personnel.</p>

            <?php if ($erreur !== ''): ?>
                <p class="connexion-erreur" role="alert"><?= htmlspecialchars($erreur) ?></p>
            <?php endif; ?>

            <form class="connexion-form" action="connexion.php" method="post">
                <label for="identifiant">Identifiant</label>
                <input type="text" name="identifiant" id="identifiant" autocomplete="username" required value="<?= htmlspecialchars($identifiant) ?>">

                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" name="mot_de_passe" id="mot_de_passe" autocomplete="current-password" required>

                <button type="submit">Envoyer</button>
            </form>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> K.A.B.L Solution Haut de France - Tous droits réservés</p>
    </footer>
</body>
</html>