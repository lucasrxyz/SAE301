<?php
require_once __DIR__ . '/../Model/verifierconnexion.php';
exigerDroit('gerer_personnel');
require_once __DIR__ . '/../Model/importerICS.php';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importer des enseignements</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="page-contenu">
        <h1>Importer des enseignements depuis un fichier ICS</h1>
        <p>Sélectionnez le personnel concerné puis un fichier ICS exporté depuis EVT. Vérifiez l’aperçu avant de lancer l’import.</p>

        <?php if ($erreurICS !== ''): ?>
            <p class="message-erreur"><?= htmlspecialchars($erreurICS) ?></p>
        <?php endif; ?>

        <?php if ($messageImport !== ''): ?>
            <p class="message-succes"><?= htmlspecialchars($messageImport) ?></p>
        <?php endif; ?>

        <form method="post" action="importerEnseignement.php" enctype="multipart/form-data">
            <label for="id_personnel">Personnel</label>
            <select name="id_personnel" id="id_personnel" required>
                <option value="">Choisir un personnel</option>
                <?php foreach ($personnelsImportables as $personnel): ?>
                    <option value="<?= htmlspecialchars((string) $personnel['id']) ?>" <?= $personnelPreselectionne === (string) $personnel['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($personnel['prenom'] . ' ' . $personnel['nom'] . ' — ' . $personnel['mail']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="fichier">Fichier ICS (5 Mo maximum)</label>
            <input type="file" name="fichier" id="fichier" accept=".ics" required>
            <button type="submit" name="previsualiser" value="1">Prévisualiser</button>
        </form>

        <?php if ($evenementsApercu): ?>
            <h2>Aperçu (<?= count($evenementsApercu) ?> événement(s))</h2>
            <div class="tableau-defilant">
                <table>
                    <tr>
                        <th>Intitulé</th>
                        <th>Date</th>
                        <th>Heure de début</th>
                        <th>Heure de fin</th>
                    </tr>
                    <?php foreach ($evenementsApercu as $evenement): ?>
                        <tr>
                            <td><?= htmlspecialchars($evenement['intitule']) ?></td>
                            <td><?= date('d/m/Y', strtotime($evenement['date_debut'])) ?></td>
                            <td><?= date('H:i', strtotime($evenement['date_debut'])) ?></td>
                            <td><?= date('H:i', strtotime($evenement['date_fin'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <form method="post" action="importerEnseignement.php">
                <input type="hidden" name="jeton" value="<?= htmlspecialchars($jetonImport) ?>">
                <button type="submit" name="importer" value="1">Importer</button>
                <a href="importerEnseignement.php">Annuler</a>
            </form>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Université Polytechnique Hauts-de-France - Tous droits réservés</p>
    </footer>
</body>
</html>
