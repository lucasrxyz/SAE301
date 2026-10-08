<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/declarerActivite.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déclarer une activité</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="page-contenu formulaire-page">
        <section class="formulaire-carte">
            <p class="formulaire-surtitre">Mes activités</p>
            <h1>Déclarer une activité individuelle</h1>

            <?php if ($erreurActivite !== ''): ?>
                <p class="message-erreur"><?= htmlspecialchars($erreurActivite) ?></p>
            <?php endif; ?>

            <?php if ($activiteEnregistree): ?>
                <p class="message-succes">L'activité a été enregistrée.</p>
            <?php endif; ?>

            <form action="" method="post" class="formulaire-vertical">
                <label for="id_type">Type d'activité</label>
                <select name="id_type" id="id_type" required>
                    <option value="1">activité1</option>
                    <option value="2">activité2</option>
                </select>

                <label for="titre">Titre de l'activité</label>
                <input type="text" name="titre" id="titre" required>

                <label for="date_debut">Date de début</label>
                <input type="date" name="date_debut" id="date_debut" required>

                <label for="date_fin">Date de fin (facultative)</label>
                <input type="date" name="date_fin" id="date_fin">

                <button type="submit" name="enregistrer">Enregistrer l'activité</button>
            </form>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> K.A.B.L Solution Hauts-de-France - Tous droits réservés</p>
    </footer>

</body>
</html>