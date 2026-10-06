<?php
    require 'config.php';

    $resultats = [];
    $aucunResultat = false;
    $nomutil = '';
    $parametreRetour = '';

    if (isset($_GET['tout'])) {
        $stmt = $pdo->prepare("SELECT * FROM Personnel");
        $stmt->execute();
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$resultats) {
            $aucunResultat = true;
        }

        $parametreRetour = 'tout=1';
    }
    elseif (isset($_GET['nom']) && $_GET['nom'] !== '') {
        $nomutil = $_GET['nom'];

        $stmt = $pdo->prepare("SELECT * FROM Personnel WHERE UPPER(nom) = UPPER(?)");
        $stmt->execute([$nomutil]);
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$resultats) {
            $aucunResultat = true;
        }

        $parametreRetour = 'nom=' . urlencode($nomutil);
    }

    $ligneChoisie = null;
    if (isset($_GET['voir'])) {
        $indexChoisi = (int) $_GET['voir'];
        if (isset($resultats[$indexChoisi])) {
            $ligneChoisie = $resultats[$indexChoisi];
        }
    }
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
            <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($nomutil) ?>">
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
                            <a href="?<?= $parametreRetour ?>&voir=<?= $index ?>">Choisir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <p>
                <a href="?"><button type="button">Fermer la liste</button></a>
            </p>
        <?php endif; ?>

        <?php if ($ligneChoisie): ?>
            <div id="modal">
                <div id="modal-contenu">
                    <table id="modal-table">
                        <tr>
                            <th>Nom</th>
                            <td><?= htmlspecialchars($ligneChoisie['nom']) ?></td>
                        </tr>
                        <tr>
                            <th>Prénom</th>
                            <td><?= htmlspecialchars($ligneChoisie['prenom']) ?></td>
                        </tr>
                        <tr>
                            <th>Mail</th>
                            <td><?= htmlspecialchars($ligneChoisie['mail']) ?></td>
                        </tr>
                        <tr>
                            <th>Date d'entrée</th>
                            <td><?= htmlspecialchars($ligneChoisie['date_entree']) ?></td>
                        </tr>
                    </table>
                    <a href="?<?= $parametreRetour ?>"><button>Fermer</button></a>
                </div>
            </div>
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

</body>
</html>