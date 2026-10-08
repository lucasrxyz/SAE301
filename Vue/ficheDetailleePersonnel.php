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

            <?php if (in_array('Gestionnaire RH', $_SESSION['roles'] ?? []) || in_array('Administrateur fonctionnel', $_SESSION['roles'] ?? [])): ?>
                <p><a href="modifierPersonnel.php?id=<?= $personnel['id'] ?>"><button type="button">Modifier les coordonnées</button></a></p>
            <?php endif; ?>

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

            <h2>Historique des statuts</h2>

            <?php if ($historiqueStatuts): ?>
                <div class="tableau-defilant">
                    <table>
                        <tr>
                            <th>Statut</th>
                            <th>Quotité recherche</th>
                            <th>Du</th>
                            <th>Au</th>
                        </tr>
                        <?php foreach ($historiqueStatuts as $statut): ?>
                            <tr>
                                <td><?= htmlspecialchars($statut['statut']) ?></td>
                                <td><?= round($statut['quotite_recherche'] * 100) ?> %</td>
                                <td><?= date('d/m/Y', strtotime($statut['date_debut'])) ?></td>
                                <td><?= $statut['date_fin'] === null ? 'En cours' : date('d/m/Y', strtotime($statut['date_fin'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php else: ?>
                <p>Aucun statut enregistré.</p>
            <?php endif; ?>

            <?php if ($personnel['date_depart'] === null): ?>
                <h2>Désactiver le personnel</h2>

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

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> K.A.B.L Solution Hauts-de-France - Tous droits réservés</p>
    </footer>

</body>
</html>