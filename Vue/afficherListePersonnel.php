<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/listepersonnel.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste de personnel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require __DIR__ . '/navbar.php'; ?>

    <div class="page-contenu">

        <h1>Bienvenue dans la page de personnel</h1>
        <p>Veuillez saisir le nom pour avoir plus d'info</p>

        <?php if (in_array('Gestionnaire RH', $_SESSION['roles'] ?? []) || in_array('Administrateur fonctionnel', $_SESSION['roles'] ?? [])): ?>
            <p><a href="ajouterpersonnel.php"><button type="button">Ajouter un personnel</button></a></p>
        <?php endif; ?>

        <form action="" method="get">
            <label for="nom">Nom du personnel</label>
            <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($nomUtilisateur) ?>">
            <button type="submit">Rechercher</button>
        </form>

        <p>
            <a href="?tout=1"><button type="button">Voir tout le personnel</button></a>
        </p>

        <?php if ($aucunResultat): ?>
            <p style="color: red;">Aucun utilisateur trouvé.</p>
        <?php endif; ?>

        <?php if ($resultats): ?>
            <div class="tableau-defilant">
                <table>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Mail</th>
                        <th>Date d'arrivée</th>
                        <th>État</th>
                        <th></th>
                    </tr>

                    <?php foreach ($resultats as $ligne): ?>
                        <tr>
                            <td><?= htmlspecialchars($ligne['nom']) ?></td>
                            <td><?= htmlspecialchars($ligne['prenom']) ?></td>
                            <td><?= htmlspecialchars($ligne['mail']) ?></td>
                            <td><?= date('d/m/Y', strtotime($ligne['date_arrivee'])) ?></td>
                            <td><?= $ligne['date_depart'] === null ? 'Actif' : 'Parti le ' . date('d/m/Y', strtotime($ligne['date_depart'])) ?></td>
                            <td>
                                <a href="ficheDetailleePersonnel.php?id=<?= $ligne['id'] ?>" target="_blank">Choisir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <p>
                <a href="?"><button type="button">Fermer la liste</button></a>
            </p>
        <?php endif; ?>

    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> K.A.B.L Solution Hauts-de-France - Tous droits réservés</p>
        <ul class="footer-liens">
            <li><a href="#">Mentions légales</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Accessibilité</a></li>
        </ul>
    </footer>

</body>
</html>
