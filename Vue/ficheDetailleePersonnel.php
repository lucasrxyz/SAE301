<?php
    require __DIR__ . '/../Model/fichepersonnel.php';
?>
<!doctype html>
<html lang="fr">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Fiche personnel</title>
        <link rel="stylesheet" href="style.css" />
    </head>
    <body>
        <div class="page-contenu">
            <?php if ($personnelIntrouvable): ?>
            <p style="color: red">Personnel introuvable.</p>
            <?php endif; ?> <?php if ($personnel): ?>
            <h1>Fiche de <?= htmlspecialchars($personnel['prenom']) ?> <?= htmlspecialchars($personnel['nom']) ?></h1>

            <table id="detail-table">
                <tr>
                    <th>Nom</th>
                    <td><?= htmlspecialchars($personnel['nom']) ?></td>
                </tr>
                <tr>
                    <th>Prénom</th>
                    <td><?= htmlspecialchars($personnel['prenom']) ?></td>
                </tr>
                <tr>
                    <th>Mail</th>
                    <td><?= htmlspecialchars($personnel['mail']) ?></td>
                </tr>
                <tr>
                    <th>Date d'entrée</th>
                    <td><?= htmlspecialchars($personnel['date_entree']) ?></td>
                </tr>
                <tr>
                    <td>
                        <button type="submit">Désactiver le personnel</button>
                    </td>
                </tr>
            </table>
            <p>
                <button type="button" onclick="window.close()">Fermer</button>
            </p>
            <?php endif; ?>
        </div>
    </body>
</html>
