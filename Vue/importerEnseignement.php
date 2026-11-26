<?php
// Vérifie que l'utilisateur est connecté
require_once __DIR__ . '/../Model/verifierconnexion.php';
// Chaque personnel importe ses propres enseignements
exigerDroit('declarer_activite');
// Lit le fichier ICS et fait l'import
require_once __DIR__ . '/../Model/importerICS.php';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importer mes enseignements</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php // Barre de navigation, commune à toutes les pages ?>
    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="page-contenu">
        <h1>Importer mes enseignements depuis un fichier ICS</h1>
        <p>Choisissez le fichier ICS de votre emploi du temps exporté depuis EVT. Vérifiez l’aperçu avant de lancer l’import : les cours déjà importés sont ignorés et les cours déplacés dans la journée sont mis à jour.</p>

        <?php // Messages d'erreur et de résultat ?>
        <?php if ($erreurICS !== ''): ?>
            <p class="message-erreur"><?= htmlspecialchars($erreurICS) ?></p>
        <?php endif; ?>

        <?php if ($messageImport !== ''): ?>
            <p class="message-succes"><?= htmlspecialchars($messageImport) ?></p>
        <?php endif; ?>

        <?php // Choix du fichier (enctype est obligatoire pour envoyer un fichier) ?>
        <form method="post" action="importerEnseignement.php" enctype="multipart/form-data">
            <label for="fichier">Fichier ICS (5 Mo maximum)</label>
            <input type="file" name="fichier" id="fichier" accept=".ics" required>
            <button type="submit" name="previsualiser" value="1">Prévisualiser</button>
        </form>

        <?php // Aperçu des événements avant l'import ?>
        <?php if ($evenementsApercu): ?>
            <h2>Aperçu (<?= count($evenementsApercu) ?> événement(s))</h2>
            <div class="tableau-defilant">
                <table>
                    <tr>
                        <th>Intitulé</th>
                        <th>Date</th>
                        <th>Heure de début</th>
                        <th>Heure de fin</th>
                        <th>Résultat</th>
                    </tr>
                    <?php foreach ($evenementsApercu as $evenement): ?>
                        <tr>
                            <td><?= htmlspecialchars($evenement['intitule']) ?></td>
                            <td><?= date('d/m/Y', strtotime($evenement['date_debut'])) ?></td>
                            <td><?= date('H:i', strtotime($evenement['date_debut'])) ?></td>
                            <td><?= date('H:i', strtotime($evenement['date_fin'])) ?></td>
                            <td><?= htmlspecialchars($evenement['statut'] === 'Doublon' ? 'Déjà importé' : $evenement['statut']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php // Bouton pour confirmer l'import, ou annuler ?>
            <form method="post" action="importerEnseignement.php">
                <button type="submit" name="importer" value="1">Importer</button>
                <a href="importerEnseignement.php">Annuler</a>
            </form>
        <?php endif; ?>
    </main>

    <?php // Pied de page ?>
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Université Polytechnique Hauts-de-France - Tous droits réservés</p>
    </footer>
</body>
</html>
