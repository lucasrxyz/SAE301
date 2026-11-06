<?php
    // Format inconnu : la page se réaffiche avec ce message
    require __DIR__ . '/../Model/verifierconnexion.php';

    // Traite le formulaire. Si un fichier est demandé, il est envoyé
    // et la page s'arrête là (exit dans le Model). Sinon, la page s'affiche.
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
            <p class="formulaire-surtitre">Rapports d'activitées</p>
            <h1>Générer Rapport</h1>

            <!-- Message d'erreur (ex. format invalide), rempli par le Model -->
            <?php if ($erreurRapport !== ''): ?>
                <p class="message-erreur"><?= htmlspecialchars($erreurRapport) ?></p>
            <?php endif; ?>

            <!-- Formulaire : format + période du rapport -->
            <form action="genererRapport.php" method="post" class="formulaire-vertical">

                <!-- Format de sortie : les valeurs doivent correspondre aux tests du Model
                 ('csv' et 'word') -->
                <label for="formatRapport">Selectionnez le format désiré</label>
                <select id="formatRapport" name="formatRapport" required>
                    <option value="">-- Choisir un format --</option>
                    <option value="csv">Format CSV</option>
                    <option value="word">Format WORD (.doc)</option>
                </select>

                <!-- Période : date + heure. Les valeurs saisies sont
                     réaffichées si la page se recharge (ex. après une erreur) -->
                    <label for="date_debut">--SPRINT3-- Début de campagne</label>
                    <input type="datetime-local" name="date_debut" id="date_debut" value="<?= htmlspecialchars($dateDebut) ?>" required>

                    <label for="date_fin">--SPRINT3-- Clôture de campagne</label>
                    <input type="datetime-local" name="date_fin" id="date_fin" value="<?= htmlspecialchars($dateFin) ?>" required>
                    
                    <!-- name="enregistrer" : c'est ce que le Model teste avec isset() -->
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