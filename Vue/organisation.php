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
        <div>Selectionnez la vue souhaitée</div>

        <div class="tableau-defilant">
            <table>
                <caption>Affichage de l'organisation</caption>
                <thead>
                    <tr>
                        <th scope="col">Département</th>
                        <th scope="col">Groupe</th>
                        <th scope="col">Personnel</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stmt as $lignes): ?>
                        <tr>
                            <td><?= htmlspecialchars($lignes['dept_nom'] ?? '') ?></td>
                            <td><?= htmlspecialchars($lignes['groupe_nom'] ?? '') ?></td>
                            <td><?= htmlspecialchars($lignes['person_nom'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> K.A.B.L Solution Hauts-de-France - Tous droits réservés</p>
    </footer>

</body>
</html>