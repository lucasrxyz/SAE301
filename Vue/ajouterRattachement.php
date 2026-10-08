<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/ajouterrattachement.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un rattachement</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require __DIR__ . '/navbar.php'; ?>

    <div class="page-contenu">
        <?php if ($personnel): ?>
            <h1>Rattacher <?= htmlspecialchars($personnel['prenom'] . ' ' . $personnel['nom']) ?></h1>

            <?php if ($rattachementAjoute): ?>
                <p style="color: green;">Le rattachement a été ajouté.</p>
            <?php endif; ?>

            <?php if ($erreurRattachement !== ''): ?>
                <p style="color: red;"><?= htmlspecialchars($erreurRattachement) ?></p>
            <?php endif; ?>

            <form action="" method="post" class="connexion-form">
                <input type="hidden" name="id_personnel" value="<?= htmlspecialchars((string) $personnel['id']) ?>">

                <label for="id_groupe">Groupe (laisser vide si département)</label>
                <select name="id_groupe" id="id_groupe">
                    <option value="">-- Aucun groupe --</option>
                    <?php foreach ($groupes as $groupe): ?>
                        <option value="<?= $groupe['id_groupe'] ?>" <?= (string) $idGroupeChoisi === (string) $groupe['id_groupe'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($groupe['nom_departement'] . ' - ' . $groupe['nom_groupe']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="id_departement">Département (laisser vide si groupe)</label>
                <select name="id_departement" id="id_departement">
                    <option value="">-- Aucun département --</option>
                    <?php foreach ($departements as $departement): ?>
                        <option value="<?= $departement['id_departement'] ?>" <?= (string) $idDepartementChoisi === (string) $departement['id_departement'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($departement['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="type_rattachement">Type de rattachement</label>
                <select name="type_rattachement" id="type_rattachement" required>
                    <option value="principal" <?= $typeRattachementChoisi === 'principal' ? 'selected' : '' ?>>Principal</option>
                    <option value="secondaire" <?= $typeRattachementChoisi === 'secondaire' ? 'selected' : '' ?>>Secondaire</option>
                </select>

                <label for="date_debut">Date de début</label>
                <input type="date" name="date_debut" id="date_debut" value="<?= htmlspecialchars($dateDebutSaisie) ?>" required>

                <label for="date_fin">Date de fin (facultative)</label>
                <input type="date" name="date_fin" id="date_fin" value="<?= htmlspecialchars($dateFinSaisie) ?>">

                <button type="submit">Ajouter le rattachement</button>
            </form>
        <?php else: ?>
            <h1>Ajouter un rattachement</h1>
            <p style="color: red;"><?= htmlspecialchars($erreurRattachement) ?></p>
        <?php endif; ?>

        <p><a href="ficheDetailleePersonnel.php?id=<?= urlencode((string) ($personnel['id'] ?? '')) ?>">Retour à la fiche</a></p>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> K.A.B.L Solution Hauts-de-France - Tous droits réservés</p>
    </footer>

</body>
</html>