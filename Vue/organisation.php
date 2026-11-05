<?php
    // Vérifie que l'utilisateur est connecté
    require __DIR__ . '/../Model/verifierconnexion.php';
    // Prépare la vue demandée
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

    <?php // Barre de navigation, commune à toutes les pages ?>
    <?php require __DIR__ . '/navbar.php'; ?>

    <div class="page-contenu">
        <h1>Bienvenue sur la page Organisation</h1>

        <?php if (verifierDroit('gerer_personnel')): ?>            <p><a href="modifierOrganisationFormulaire.php"><button type="button">Modifier organisation</button></a></p>
        <?php endif; ?>

        <p>Veuillez selectionner la vue désirée</p>

        <?php // Choix de la date et de la vue ?>
        <form action="" method="get" class="form">
            <label for="date">Situation au</label>
            <input type="date" name="date" id="date" value="<?= htmlspecialchars($dateConsultee) ?>" required>

            <label>Vues disponibles </label>            <button type="submit" name="vue" value="departements">Départements</button>
            <button type="submit" name="vue" value="groupes">Groupes</button>
            <button type="submit" name="vue" value="hierarchie">Hiérarchie</button>
        </form>

        <?php // Le tableau s'affiche une fois la vue choisie ?>
        <?php if ($vue !== null): ?>
        <h2>Situation au <?= date('d/m/Y', strtotime($dateConsultee)) ?></h2>
        <div class="tableau-defilant">
            <?php // Les colonnes dépendent de la vue ?>
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
                    <?php // Une ligne par résultat ?>
                    <?php foreach ($stmt as $lignes): ?>
                        <tr>
                            <?php if ($vue === "departements"): ?>
                            <td><?= htmlspecialchars($lignes['dept_nom']) ?></td>
                            <td><?= htmlspecialchars($lignes['prenom'] . ' ' . $lignes['nom']) ?></td>

                            <?php elseif ($vue === "groupes"): ?>
                            <td><?= htmlspecialchars($lignes['dept_nom']) ?></td>
                            <td><?= htmlspecialchars($lignes['groupe_nom']) ?></td>
                            <td><?= htmlspecialchars($lignes['prenom'] . ' ' . $lignes['nom']) ?></td>
                            <td>
                                <?php if (!$lignes['responsable_groupe']): ?>
                                    Membre
                                <?php // Responsable du groupe, sinon du département ?>
                                <?php elseif ($lignes['id_groupe'] !== null): ?>
                                    Responsable du groupe
                                <?php else: ?>
                                    Responsable du département
                                <?php endif; ?>
                            </td>

                            <?php else: ?>
                            <td><?= htmlspecialchars($lignes["nom_role"])?></td>
                            <td><?= htmlspecialchars($lignes['prenom'] . ' ' . $lignes['nom']) ?></td>
                            <td><?= htmlspecialchars($lignes["affectation"])?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php // Pied de page ?>
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