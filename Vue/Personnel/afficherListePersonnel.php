<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    exigerDroit('voir_personnel');
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
        <p>Recherchez par nom (actuel ou ancien), ORCID ou IdHAL</p>

        <form action="" method="get">
            <label for="nom">Rechercher</label>
            <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($nomUtilisateur) ?>">
            <button type="submit">Rechercher</button>
        </form>

        <p>
            <a href="?tout=1"><button type="button">Voir tout le personnel</button></a>
            <a href="?rattaches=1"><button type="button">Voir le personnel rattaché</button></a>
            <?php if (verifierDroit('gerer_personnel')): ?>
                <a href="ajouterpersonnel.php"><button type="button">Ajouter un personnel</button></a>
            <?php endif; ?>
        </p>

        <?php if ($aucunResultat): ?>
            <p class="message-erreur">Aucun utilisateur trouvé.</p>
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
                            <td>
                                <?= htmlspecialchars($ligne['nom']) ?>
                                <?php if (in_array($ligne['nom'] . ' ' . $ligne['prenom'], $homonymes)): ?>
                                    <span class="badge-homonyme">Homonyme</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($ligne['prenom']) ?></td>
                            <td><?= htmlspecialchars($ligne['mail']) ?></td>
                            <td><?= date('d/m/Y', strtotime($ligne['date_arrivee'])) ?></td>
                            <td>
                                <?php if ($ligne['sans_rattachement']): ?>
                                    <span class="badge-homonyme">Non rattaché</span>
                                <?php elseif ($ligne['date_depart'] === null): ?>
                                    Actif
                                <?php else: ?>
                                    Parti le <?= date('d/m/Y', strtotime($ligne['date_depart'])) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="ficheDetailleePersonnel.php?id=<?= $ligne['id'] ?>" target="_blank">Choisir</a>
                                <?php if ($ligne['sans_rattachement'] && verifierDroit('gerer_personnel')): ?>
                                    · <a href="ajouterRattachement.php?id=<?= $ligne['id'] ?>">Rattacher</a>
                                <?php endif; ?>
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
        <p>&copy; <?= date('Y') ?> Université Polytechnique Hauts-de-France - Tous droits réservés</p>
        <ul class="footer-liens">
            <li><a href="#">Mentions légales</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Accessibilité</a></li>
        </ul>
    </footer>

</body>
</html>