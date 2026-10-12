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

    <main class="formulaire-page">
        <section class="formulaire-carte">
            <?php if ($personnel): ?>
                <p class="formulaire-surtitre">Rattachement</p>
                <h1><?= htmlspecialchars($personnel['prenom'] . ' ' . $personnel['nom']) ?></h1>
                <p>Rattachez ce personnel à un groupe ou à un département.</p>

                <?php if ($rattachementAjoute): ?>
                    <p class="message-succes">Le rattachement a été ajouté.</p>
                <?php endif; ?>

                <?php if ($erreurRattachement !== ''): ?>
                    <p class="message-erreur"><?= htmlspecialchars($erreurRattachement) ?></p>
                <?php endif; ?>

                <form action="" method="post" class="formulaire-vertical">
                    <input type="hidden" name="id_personnel" value="<?= htmlspecialchars((string) $personnel['id']) ?>">

                    <label for="id_groupe">Groupe</label>
                    <select name="id_groupe" id="id_groupe">
                        <option value="">-- Aucun groupe --</option>
                        <?php foreach ($groupes as $groupe): ?>
                            <option value="<?= $groupe['id_groupe'] ?>" <?= (string) $idGroupeChoisi === (string) $groupe['id_groupe'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($groupe['nom_departement'] . ' - ' . $groupe['nom_groupe']) ?><?= $groupe['date_fin'] ? ' (clôturé le ' . date('d/m/Y', strtotime($groupe['date_fin'])) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="aide">Laisser vide pour un rattachement à un département.</p>

                    <label for="id_departement">Département</label>
                    <select name="id_departement" id="id_departement">
                        <option value="">-- Aucun département --</option>
                        <?php foreach ($departements as $departement): ?>
                            <option value="<?= $departement['id_departement'] ?>" <?= (string) $idDepartementChoisi === (string) $departement['id_departement'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($departement['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="aide">Laisser vide pour un rattachement à un groupe.</p>

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

                <p><a href="ficheDetailleePersonnel.php?id=<?= urlencode((string) $personnel['id']) ?>">Retour à la fiche</a></p>
            <?php else: ?>
                <h1>Ajouter un rattachement</h1>
                <p class="message-erreur"><?= htmlspecialchars($erreurRattachement) ?></p>
                <p><a href="afficherListePersonnel.php">Retour à la liste</a></p>
            <?php endif; ?>
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