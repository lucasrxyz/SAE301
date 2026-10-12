<?php
require __DIR__ . '/../Model/verifierconnexion.php';
require __DIR__ . '/../Model/modifierpersonnel.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un personnel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="formulaire-page">
        <section class="formulaire-carte">
            <?php if ($personnelIntrouvable): ?>
                <h1>Modifier un personnel</h1>
                <p class="message-erreur">Personnel introuvable.</p>
                <p><a href="afficherListePersonnel.php">Retour à la liste</a></p>
            <?php else: ?>
                <p class="formulaire-surtitre">Coordonnées</p>
                <h1><?= htmlspecialchars($personnel['prenom'] . ' ' . $personnel['nom']) ?></h1>
                <p>Modifiez le nom, le prénom ou l'adresse mail de ce personnel.</p>

                <?php if ($modificationReussie): ?>
                    <p class="message-succes">Les coordonnées ont été modifiées.</p>
                <?php endif; ?>

                <?php if ($erreurModification !== ''): ?>
                    <p class="message-erreur"><?= htmlspecialchars($erreurModification) ?></p>
                <?php endif; ?>

                <form action="" method="post" class="formulaire-vertical">
                    <input type="hidden" name="id_personnel" value="<?= $personnel['id'] ?>">

                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" maxlength="50" value="<?= htmlspecialchars($personnel['nom']) ?>" required>
                    <p class="aide">Si le nom change, l'ancien nom est gardé dans l'historique.</p>

                    <label for="prenom">Prénom</label>
                    <input type="text" name="prenom" id="prenom" maxlength="50" value="<?= htmlspecialchars($personnel['prenom']) ?>" required>

                    <label for="mail">Mail</label>
                    <input type="email" name="mail" id="mail" maxlength="60" value="<?= htmlspecialchars($personnel['mail']) ?>" required>

                    <button type="submit">Enregistrer les modifications</button>
                </form>

                <p><a href="ficheDetailleePersonnel.php?id=<?= $personnel['id'] ?>">Retour à la fiche</a></p>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Université Polytechnique Hauts-de-France - Tous droits réservés</p>
        <ul class="footer-liens">
            <li><a href="#">Mentions légales</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Accessibilité</a></li>
        </ul>
    </footer>

</body>
</html>