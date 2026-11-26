<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste de personnel</title>
    <link rel="stylesheet" href="Vue/style.css">
</head>
<body>

    <?php // Barre de navigation, commune à toutes les pages ?>
    <?php require __DIR__ . '/navbar.php'; ?>

    <div class="page-contenu">

        <h1>Bienvenue dans la page de personnel</h1>
        <p>Recherchez par nom (actuel ou ancien), ORCID ou IdHAL</p>

        <?php // Recherche par nom, et boutons pour tout voir ?>
        <form action="" method="get">
            <input type="hidden" name="page" value="afficherListePersonnel">
            <label for="nom">Rechercher</label>
            <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($nomUtilisateur) ?>">
            <button type="submit">Rechercher</button>
        </form>

        <p>
            <a href="?page=afficherListePersonnel&tout=1"><button type="button">Voir tout le personnel</button></a>
            <a href="?page=afficherListePersonnel&rattaches=1"><button type="button">Voir le personnel rattaché</button></a>
            <?php if ($peutGererPersonnel): ?>
                <a href="index.php?page=ajouterpersonnel"><button type="button">Ajouter un personnel</button></a>
            <?php endif; ?>
        </p>

        <?php // Message si la recherche ne trouve rien ?>
        <?php if ($aucunResultat): ?>
            <p class="message-erreur">Aucun utilisateur trouvé.</p>
        <?php endif; ?>

        <?php // Tableau des résultats ?>
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
                                <?php // État de la personne : sans rattachement, présente ou partie ?>
                                <?php if ($ligne['sans_rattachement']): ?>
                                    <span class="badge-homonyme">Non rattaché</span>
                                <?php elseif ($ligne['date_depart'] === null): ?>
                                    Actif
                                <?php else: ?>
                                    Parti le <?= date('d/m/Y', strtotime($ligne['date_depart'])) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="index.php?page=ficheDetailleePersonnel&id=<?= $ligne['id'] ?>" target="_blank">Choisir</a>
                                <?php // Lien pour ajouter un rattachement si elle n'en a pas ?>
                                <?php if ($ligne['sans_rattachement'] && $peutGererPersonnel): ?>
                                    · <a href="index.php?page=ajouterRattachement&id=<?= $ligne['id'] ?>">Rattacher</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <p>
                <a href="?page=afficherListePersonnel"><button type="button">Fermer la liste</button></a>
            </p>
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