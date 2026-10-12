<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
require __DIR__ . '/../Model/listeGroupesPeriode.php';
?>
<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Groupes par période</title>
        <link rel="stylesheet" href="style.css" />
    </head>
    <body>
        <?php require __DIR__ . '/navbar.php'; ?>

        <div class="page-contenu">
            <h1>Groupes présents sur une période</h1>

            <form action="" method="get" class="form">
                <label>Du <input type="date" name="debut" value="<?= htmlspecialchars($debut) ?>" required /></label>
                <label>au <input type="date" name="fin" value="<?= htmlspecialchars($fin) ?>" required /></label>
                <button type="submit">Afficher</button>
            </form>

            <?php if ($debut > $fin): ?>
            <p>La date de début doit être avant la date de fin.</p>

            <?php elseif (empty($stmt)): ?>
            <p>Aucun groupe présent sur cette période.</p>

            <?php else: ?>
            <div class="tableau-defilant">
                <table>
                    <thead>
                        <tr>
                            <th>Département</th>
                            <th>Groupe</th>
                            <th>Début</th>
                            <th>Fin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stmt as $lignes): ?>
                        <tr>
                            <td><?= htmlspecialchars($lignes['dept_nom']) ?></td>
                            <td><?= htmlspecialchars($lignes['groupe_nom']) ?></td>
                            <td><?= htmlspecialchars($lignes['date_debut']) ?></td>
                            <td><?= htmlspecialchars($lignes['date_fin']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

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
