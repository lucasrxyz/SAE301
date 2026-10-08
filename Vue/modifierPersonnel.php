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

    <div class="page-contenu">
        <h1>Modifier les coordonnées du personnel</h1>

        <?php if ($personnelIntrouvable): ?>
            <p style="color: red;">Personnel introuvable.</p>
        <?php else: ?>
            <?php if ($modificationReussie): ?>
                <p style="color: green;">Les coordonnées ont été modifiées.</p>
            <?php endif; ?>

            <?php if ($erreurModification !== ''): ?>
                <p style="color: red;"><?= htmlspecialchars($erreurModification) ?></p>
            <?php endif; ?>

            <form action="" method="post" class="connexion-form">
                <input type="hidden" name="id_personnel" value="<?= htmlspecialchars((string) $personnel['id']) ?>">

                <label for="nom">Nom</label>
                <input type="text" name="nom" id="nom" maxlength="50" value="<?= htmlspecialchars($personnel['nom']) ?>" required>

                <label for="prenom">Prénom</label>
                <input type="text" name="prenom" id="prenom" maxlength="50" value="<?= htmlspecialchars($personnel['prenom']) ?>" required>

                <label for="mail">Mail</label>
                <input type="email" name="mail" id="mail" maxlength="60" value="<?= htmlspecialchars($personnel['mail']) ?>" required>

                <button type="submit">Enregistrer les modifications</button>
            </form>

            <p><a href="ficheDetailleePersonnel.php?id=<?= $personnel['id'] ?>">Retour à la fiche</a></p>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> K.A.B.L Solution Hauts-de-France - Tous droits réservés</p>
    </footer>

</body>
</html>