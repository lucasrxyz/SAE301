<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/listeOrganisation.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organisation</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require __DIR__ . '/navbar.php'; ?>

    <div class="page-contenu">
        <h1>Bienvenue sur la page Organisation</h1>

        <p>Veuillez selectionner la vue désirée</p>

        <form action="" method="get">
            <label>Vues disponibles </label>
            <button type="submit" name="vue" value="departements">Départements</button>
            <button type="submit" name="vue" value="groupes">Groupes</button>
            <button type="submit" name="vue" value="hierarchie">Hiérarchie</button>
        </form>

        <?php if ($vue !== null): ?>
        <div class="tableau-defilant">
            <table>
                <thead>
                    <tr>
                        <?php if ($vue === "departements"): ?>
                        <th>Département</th>
                        <th>Personnel</th>

                        <?php elseif ($vue === "groupes"): ?>
                        <th>Département</th>
                        <th>Groupe</th>
                        <th>Personnel</th>
                        <th>Statut</th>

                        <?php else: ?>
                            <th>Rôle</th>
                            <th>Personnel</th>
                            <th>Affectation</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stmt as $lignes): ?>
                        <tr>
                            <?php if ($vue === "departements"): ?>
                            <td><?= htmlspecialchars($lignes['dept_nom']) ?></td>
                            <td><?= htmlspecialchars($lignes['person_nom']) ?></td>

                            <?php elseif ($vue === "groupes"): ?>
                            <td><?= htmlspecialchars($lignes['dept_nom']) ?></td>
                            <td><?= htmlspecialchars($lignes['groupe_nom']) ?></td>
                            <td><?= htmlspecialchars($lignes['person_nom'])?></td>
                            <td><?= htmlspecialchars($lignes['statut_groupe'])?></td>

                            <?php else: ?>
                            <td><?= htmlspecialchars($lignes["nom_role"])?></td>
                            <td><?= htmlspecialchars($lignes["person_nom"])?></td>
                            <td><?= htmlspecialchars($lignes["affectation"])?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> K.A.B.L Solution Hauts-de-France - Tous droits réservés</p>
    </footer>

</body>
</html>