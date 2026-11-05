<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/declarerCollective.php';
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
            <h1>Déclarer une activité collective</h1>

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
                    <?php foreach ($typesActivite as $type): ?>
                        <option value="<?= $type['id_type'] ?>" <?= $typeactivite == $type['id_type'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['categorie'] . ' - ' . $type['nom_type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="titre">Titre de l'activité</label>
                <input type="text" name="titre" id="titre" value="<?= htmlspecialchars($titre) ?>" required>

                <label for="date_debut">Début</label>
                <input type="datetime-local" name="date_debut" id="date_debut" value="<?= htmlspecialchars($datedebut) ?>" required>

                <label for="date_fin">Fin</label>
                <input type="datetime-local" name="date_fin" id="date_fin" value="<?= htmlspecialchars($datefin) ?>">
                <p class="aide">Laisser vide si l'activité n'a pas de durée (relecture, jury…).</p>

                <label for="description">Description (revue, lieu, organisme…)</label>
                <textarea name="description" id="description" rows="3"><?= htmlspecialchars($description) ?></textarea>

                <label for="collaborateurs">Sélectionnez vos collaborateurs</label>
                <select name="collaborateurs[]" id="collaborateurs" multiple size="5">
                    <?php foreach ($listePersonnels as $personnel): ?>
                        <option value="<?= $personnel['id'] ?>" <?= in_array($personnel['id'], $collaborateurs) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($personnel['nom'] . ' ' . $personnel['prenom'] . ' [idhal: ' . $personnel['id_idhal'] . '] [orcid: ' . $personnel['id_orcid'] . ']') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="aide">Ctrl + clic pour sélectionner plusieurs.</p>

                <label class="case-a-cocher">
                    <input type="checkbox" name="en_cours" <?= $enCours ? 'checked' : '' ?>>
                    Activité toujours en cours
                </label>

                <button type="submit" name="enregistrer">Enregistrer l'activité</button>
            </form>
        </section>
    </main>

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