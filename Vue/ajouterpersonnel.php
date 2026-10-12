<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/ajouterpersonnel.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un personnel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="page-contenu formulaire-page">
        <section class="formulaire-carte">
            <p class="formulaire-surtitre">Gestion du personnel</p>
            <h1>Ajouter un personnel</h1>

            <?php if ($personnelCree): ?>
                <p class="message-succes">Le personnel a été ajouté.</p>
            <?php endif; ?>

            <?php if ($erreurPersonnel !== ''): ?>
                <p class="message-erreur"><?= htmlspecialchars($erreurPersonnel) ?></p>
            <?php endif; ?>

            <form action="" method="post" class="formulaire-vertical">
                <label for="nom">Nom</label>
                <input type="text" name="nom" id="nom" maxlength="50" value="<?= htmlspecialchars($nomSaisi) ?>" required>

                <label for="prenom">Prénom</label>
                <input type="text" name="prenom" id="prenom" maxlength="50" value="<?= htmlspecialchars($prenomSaisi) ?>" required>

                <label for="mail">Mail</label>
                <input type="email" name="mail" id="mail" maxlength="60" value="<?= htmlspecialchars($mailSaisi) ?>" required>

                <label for="date_arrivee">Date d'arrivée</label>
                <input type="date" name="date_arrivee" id="date_arrivee" value="<?= htmlspecialchars($dateArriveeSaisie) ?>" required>

                <label for="statut">Statut</label>
                    <select name="statut" id="statut" required>
                        <option value="">-- Choisir un statut --</option>
                             <?php foreach ($statutsPossibles as $statut): ?>
                                <option value="<?= htmlspecialchars($statut) ?>" <?= $statutSaisi === $statut ? 'selected' : '' ?>><?= htmlspecialchars($statut) ?></option>
                            <?php endforeach; ?>
                    </select>

                <label for="quotite">Quotité recherche (%)</label>
                <input type="number" name="quotite" id="quotite" min="0" max="100" step="0.01" value="<?= htmlspecialchars($quotiteSaisie) ?>" required>

                <button type="submit">Ajouter le personnel</button>
            </form>

            <p><a href="afficherListePersonnel.php">Retour à la liste du personnel</a></p>
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