<?php
    // Vérifie que l'utilisateur est connecté
    require __DIR__ . '/../Model/verifierconnexion.php';
    // Interroge HAL et fait l'import
    require __DIR__ . '/../Model/importerHAL.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importer des publications</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php // Barre de navigation, commune à toutes les pages ?>
    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="page-contenu formulaire-page">
        <section class="formulaire-carte">
            <p class="formulaire-surtitre">Publications HAL</p>
            <h1>Importer des publications</h1>

            <?php // Messages d'erreur et de réussite ?>
            <?php if ($erreurImport !== ''): ?>
                <p class="message-erreur"><?= htmlspecialchars($erreurImport) ?></p>
            <?php endif; ?>

            <?php if ($nbImportees >= 0): ?>
                <p class="message-succes"><?= $nbImportees ?> publication(s) importée(s).</p>
            <?php endif; ?>

            <?php // Choix du personnel, parmi ceux qui ont un IdHAL ?>
            <?php if ($personnels): ?>
                <form action="" method="post" class="formulaire-vertical">
                    <label for="id_personnel">Personnel (IdHAL renseigné)</label>
                    <select name="id_personnel" id="id_personnel" required>
                        <option value="">-- Choisir un personnel --</option>
                        <?php foreach ($personnels as $personnel): ?>
                            <option value="<?= $personnel['id'] ?>" <?= $idPersonnel == $personnel['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($personnel['prenom'] . ' ' . $personnel['nom'] . ' (' . $personnel['id_idhal'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" name="apercu">Afficher l'aperçu</button>
                </form>
            <?php else: ?>
                <p>Aucun personnel n'a d'IdHAL renseigné.</p>
            <?php endif; ?>
        </section>

        <?php // Aperçu des publications trouvées ?>
        <?php if ($apercuAffiche): ?>
            <section class="liste-activites">
                <h2>Aperçu des publications</h2>

                <?php if ($publications): ?>
                    <p class="aide"><?= count($publications) ?> publication(s) trouvée(s), dont <?= $nbNouvelles ?> nouvelle(s).</p>

                    <div class="tableau-defilant">
                        <?php // Tableau de l'aperçu, avec le statut de chaque publication ?>
                        <table>
                            <tr>
                                <th>Titre</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Statut</th>
                            </tr>
                            <?php foreach ($publications as $publication): ?>
                                <tr>
                                    <td><?= htmlspecialchars($publication['titre']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($publication['date'])) ?></td>
                                    <td><?= htmlspecialchars($publication['type']) ?></td>
                                    <td><?= $publication['deja_presente'] ? 'Déjà présente' : 'Nouvelle' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>

                    <?php // Nombre de documents ignorés ?>
                    <?php if ($nbAutresTypes > 0): ?>
                        <p class="aide"><?= $nbAutresTypes ?> autre(s) document(s) HAL ignoré(s) (type non géré).</p>
                    <?php endif; ?>

                    <?php // Le bouton d'import s'affiche seulement s'il y a des nouveautés ?>
                    <?php if ($nbNouvelles > 0): ?>
                        <form action="" method="post" class="formulaire-vertical">
                            <input type="hidden" name="id_personnel" value="<?= $idPersonnel ?>">
                            <button type="submit" name="importer">Importer les <?= $nbNouvelles ?> nouvelle(s) publication(s)</button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Aucune publication trouvée.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

</body>
</html>
