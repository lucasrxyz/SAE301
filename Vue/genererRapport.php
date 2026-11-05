<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/genererRapport.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générer votre rapport</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="page-contenu formulaire-page">
        <section class="formulaire-carte">
            <p class="formulaire-surtitre">Mes activités</p>
            <h1>Générer Rapport</h1>

            <?php if ($erreurRapport !== ''): ?>
                <p class="message-erreur"><?= htmlspecialchars($erreurRapport) ?></p>
            <?php endif; ?>

            <form action="genererRapport.php" method="post" class="formulaire-vertical">
                <label for="formatRapport">Selectionnez le format désiré</label>
                <select id="formatRapport" name="formatRapport" required>
                    <option value="">-- Choisir un format --</option>
                    <option value="csv">Format CSV</option>
                    <option value="word">Format WORD (.doc)</option>
                </select>
                    <label for="date_debut">--SPRINT3-- Début de campagne</label>
                    <input type="datetime-local" name="date_debut" id="date_debut" value="<?= htmlspecialchars($dateDebut) ?>" required>

                    <label for="date_fin">--SPRINT3-- Clôture de campagne</label>
                    <input type="datetime-local" name="date_fin" id="date_fin" value="<?= htmlspecialchars($dateFin) ?>" required>
                    <button type="submit" name="enregistrer">Générer mon rapport</button>
            </form>
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