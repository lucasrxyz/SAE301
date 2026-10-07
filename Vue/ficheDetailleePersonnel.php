<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/desactiverpersonnel.php';
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

    <?php require __DIR__ . '/navbar.php'; ?>

    <div class="page-contenu">

        <?php if ($personnelIntrouvable): ?>
            <p style="color: red">Personnel introuvable.</p>
        <?php endif; ?>

        <?php if ($personnelDesactive): ?>
            <p style="color: green">Le personnel a été désactivé.</p>
        <?php endif; ?>

        <?php if ($erreurDesactivation): ?>
            <p style="color: red"><?= htmlspecialchars($erreurDesactivation) ?></p>
        <?php endif; ?>

        <?php if ($personnel): ?>
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
                    <th>ORCID</th>
                    <td><?= htmlspecialchars($personnel['id_orcid'] ?? 'Non renseigné') ?></td>
                </tr>
                <tr>
                    <th>IdHAL</th>
                    <td><?= htmlspecialchars($personnel['id_idhal'] ?? 'Non renseigné') ?></td>
                </tr>
                <tr>
                    <th>Date d'arrivée</th>
                    <td><?= date('d/m/Y', strtotime($personnel['date_arrivee'])) ?></td>
                </tr>
                <tr>
                    <th>État</th>
                    <td><?= $personnel['date_depart'] === null ? 'Actif' : 'Parti le ' . date('d/m/Y', strtotime($personnel['date_depart'])) ?></td>
                </tr>
            </table>

            <?php if ($personnel['date_depart'] === null): ?>
                <form method="post" action="">
                    <input type="hidden" name="id_personnel" value="<?= $personnel['id'] ?>">

                    <label for="date_depart">Date de départ</label>
                    <input type="date" name="date_depart" id="date_depart" value="<?= date('Y-m-d') ?>" required>

                    <label>
                        <input type="checkbox" name="confirmation" required>
                        Je confirme la désactivation
                    </label>

                    <button type="submit" name="desactiver" value="true">Désactiver le personnel</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <p>
            <a href="afficherListePersonnel.php?tout=1">Retour à la liste</a>
        </p>

    </div>

</body>
</html>
