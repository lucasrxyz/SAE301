<?php
    require __DIR__ . '/../Model/listepersonnel.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste de personnel</title>
    <link rel="stylesheet" href="style.css">
</head><body>

    <nav class="navbar">
        <div class="navbar-logo">
            K.A.B.L Solution <span>Hauts-de-France</span>
        </div>
        <ul class="navbar-liens">
            <li><a href="#">Accueil</a></li>
            <li><a href="#">Personnel</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
    </nav>

    <div class="page-contenu">

        <h1>Bienvenue dans la page de personnel</h1>
        <p>Veuillez saisir le nom pour avoir plus d'info</p>

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
            <table>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Mail</th>
                    <th>Date d'entrée</th>
                    <th></th>
                </tr>

                <?php foreach ($resultats as $index => $ligne): ?>
                    <tr>
                        <td><?= htmlspecialchars($ligne['nom']) ?></td>
                        <td><?= htmlspecialchars($ligne['prenom']) ?></td>
                        <td><?= htmlspecialchars($ligne['mail']) ?></td>
                        <td><?= htmlspecialchars($ligne['date_entree']) ?></td>
                        <td>
                            <a href="javascript:void(0)" onclick="ouvrirFichePersonnel(<?= $ligne['id'] ?>)">Choisir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <p>
                <a href="?"><button type="button">Fermer la liste</button></a>
            </p>
        <?php endif; ?>

    </div>
    <footer class="footer">
        <p>&copy; <?= date('Y') ?> K.A.B.L Solution Haut de France - Tous droits réservés</p>
        <ul class="footer-liens">
            <li><a href="#">Mentions légales</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Accessibilité</a></li>
        </ul>
    </footer>

    <script>
    function ouvrirFichePersonnel(id) {
        window.open(
            'ficheDetailleePersonnel.php?id=' + id,
            'fichePersonnel',
            'width=450,height=400,scrollbars=yes,resizable=yes'
        );
    }
    </script>

</body>
</html>