<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/declarerActivite.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déclarer une activité</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="page-contenu formulaire-page">
        <section class="formulaire-carte">
            <p class="formulaire-surtitre">Mes activités</p>
            <h1>Déclarer une activité individuelle</h1>

            <?php if ($erreurActivite !== ''): ?>
                <p class="message-erreur"><?= htmlspecialchars($erreurActivite) ?></p>
            <?php endif; ?>

            <?php if ($activiteEnregistree): ?>
                <p class="message-succes">L'activité a été enregistrée.</p>
            <?php endif; ?>

            <form action="" method="post" class="formulaire-vertical">
                <label for="id_type">Type d'activité</label>
                <select name="id_type" id="id_type" required>
                    <option value="">-- Choisir un type --</option>
                    <?php foreach ($typesIndividuels as $type): ?>
                        <option value="<?= $type['id_type'] ?>" <?= $typeactivite == $type['id_type'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['categorie'] . ' - ' . $type['nom_type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="titre">Titre de l'activité</label>
                <input type="text" name="titre" id="titre" value="<?= htmlspecialchars($titre) ?>" required>

                <label for="date_debut">Date de début</label>
                <input type="date" name="date_debut" id="date_debut" value="<?= htmlspecialchars($datedebut) ?>" required>

                <label for="date_fin">Date de fin (facultative)</label>
                <input type="date" name="date_fin" id="date_fin" value="<?= htmlspecialchars($datefin) ?>">

                <button type="submit" name="enregistrer">Enregistrer l'activité</button>
            </form>
        </section>

        <section class="liste-activites">
            <h2>Mes activités déclarées</h2>

            <?php if ($mesActivites): ?>
                <div class="tableau-defilant">
                    <table>
                        <tr>
                            <th>Catégorie</th>
                            <th>Type</th>
                            <th>Titre</th>
                            <th>Du</th>
                            <th>Au</th>
                        </tr>
                        <?php foreach ($mesActivites as $activite): ?>
                            <tr>
                                <td><?= htmlspecialchars($activite['categorie']) ?></td>
                                <td><?= htmlspecialchars($activite['nom_type']) ?></td>
                                <td><?= htmlspecialchars($activite['titre']) ?></td>
                                <td><?= date('d/m/Y', strtotime($activite['date_debut'])) ?></td>
                                <td><?= $activite['date_fin'] === null ? 'En cours' : date('d/m/Y', strtotime($activite['date_fin'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php else: ?>
                <p>Vous n'avez encore déclaré aucune activité.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> K.A.B.L Solution Hauts-de-France - Tous droits réservés</p>
    </footer>

</body>
</html>