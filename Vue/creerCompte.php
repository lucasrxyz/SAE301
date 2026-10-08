<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/creercompte.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="page-contenu formulaire-page">
        <section class="formulaire-carte">
            <p class="formulaire-surtitre">Gestion des comptes</p>
            <h1>Créer un compte</h1>
            <p>Donnez un accès à l'application à un membre du personnel.</p>

            <?php if ($compteCree): ?>
                <p class="message-succes">Le compte a été créé.</p>
            <?php endif; ?>

            <?php if ($erreurCompte !== ''): ?>
                <p class="message-erreur"><?= htmlspecialchars($erreurCompte) ?></p>
            <?php endif; ?>

            <?php if ($personnelsSansCompte): ?>
                <form action="" method="post" class="formulaire-vertical">
                    <label for="id_personnel">Personnel</label>
                    <select name="id_personnel" id="id_personnel" required>
                        <option value="">-- Choisir un personnel --</option>
                        <?php foreach ($personnelsSansCompte as $ligne): ?>
                            <option value="<?= $ligne['id'] ?>" <?= $idPersonnelChoisi == $ligne['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ligne['nom'] . ' ' . $ligne['prenom'] . ' (' . $ligne['mail'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="identifiant">Identifiant</label>
                    <input type="text" name="identifiant" id="identifiant" value="<?= htmlspecialchars($identifiantSaisi) ?>" required>

                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" name="mot_de_passe" id="mot_de_passe" minlength="8" required>
                    <p class="aide">8 caractères minimum.</p>

                    <label for="id_role">Rôle</label>
                    <select name="id_role" id="id_role" required>
                        <?php foreach ($listeRoles as $role): ?>
                            <option value="<?= $role['id_role'] ?>" <?= $idRoleChoisi == $role['id_role'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['nom_role']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit">Créer le compte</button>
                </form>
            <?php else: ?>
                <p>Tous les personnels actifs ont déjà un compte.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> K.A.B.L Solution Hauts-de-France - Tous droits réservés</p>
    </footer>

</body>
</html>