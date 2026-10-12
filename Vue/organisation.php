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

        <?php if (in_array('Gestionnaire RH', $_SESSION['roles'] ?? []) || in_array('Administrateur fonctionnel', $_SESSION['roles'] ?? [])): ?>
            <p><a href="modifierOrganisationFormulaire.php"><button type="button">Modifier organisation</button></a></p>
        <?php endif; ?>

        <p>Veuillez selectionner la vue désirée</p>

        <form action="" method="get" class="form">
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
        <p>&copy; <?= date('Y') ?> Université Polytechnique Hauts-de-France - Tous droits réservés</p>
        <ul class="footer-liens">
            <li><a href="#">Mentions légales</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Accessibilité</a></li>
        </ul>
    </footer>

</body>
</html>