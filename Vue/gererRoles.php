<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    exigerDroit('gerer_roles');
    require __DIR__ . '/../Model/gererroles.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des rôles</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="page-contenu">
        <h1>Gestion des rôles</h1>
        <p>Attribuez ou retirez les rôles de chaque compte. Un compte sans rôle n'a accès à rien.</p>

        <?php if ($messageRole): ?>
            <p class="message-succes"><?= htmlspecialchars($messageRole) ?></p>
        <?php endif; ?>

        <?php if ($erreurRole): ?>
            <p class="message-erreur"><?= htmlspecialchars($erreurRole) ?></p>
        <?php endif; ?>

        <div class="tableau-defilant">
            <table>
                <tr>
                    <th>Personne</th>
                    <th>Identifiant</th>
                    <th>Rôles actuels</th>
                    <th>Ajouter un rôle</th>
                </tr>
                <?php foreach ($comptes as $compte): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars(trim(($compte['prenom'] ?? '') . ' ' . ($compte['nom'] ?? ''))) ?>
                            <?php if (!$compte['actif']): ?>
                                <span class="bord-etat bord-etat-parti">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($compte['identifiant']) ?></td>
                        <td>
                            <?php if (empty($rolesParCompte[$compte['id_compte']])): ?>
                                <span class="aide">Aucun rôle</span>
                            <?php else: ?>
                                <?php foreach ($rolesParCompte[$compte['id_compte']] as $role): ?>
                                    <form method="post" action="" class="role-pastille">
                                        <input type="hidden" name="id_compte" value="<?= $compte['id_compte'] ?>">
                                        <input type="hidden" name="id_role" value="<?= $role['id_role'] ?>">
                                        <?= htmlspecialchars($role['nom_role']) ?>
                                        <button type="submit" name="retirer_role" title="Retirer ce rôle">×</button>
                                    </form>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" action="" class="role-ajout">
                                <input type="hidden" name="id_compte" value="<?= $compte['id_compte'] ?>">
                                <select name="id_role" required>
                                    <option value="">Choisir</option>
                                    <?php foreach ($tousLesRoles as $role): ?>
                                        <option value="<?= $role['id_role'] ?>"><?= htmlspecialchars($role['nom_role']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="ajouter_role">Ajouter</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <h2>Grille des droits</h2>
        <div class="tableau-defilant">
            <table>
                <tr>
                    <th>Rôle</th>
                    <th>Niveau</th>
                    <th>Ce qu'il peut faire</th>
                </tr>
                <tr><td>Administrateur fonctionnel</td><td>0</td><td>Tout, plus la gestion des rôles</td></tr>
                <tr><td>Directeur de laboratoire</td><td>1</td><td>Tableau de bord et personnel de tout le laboratoire</td></tr>
                <tr><td>Responsable de département</td><td>2</td><td>Tableau de bord et personnel de son département</td></tr>
                <tr><td>Responsable de groupe</td><td>3</td><td>Tableau de bord et personnel de son groupe</td></tr>
                <tr><td>Gestionnaire RH</td><td>3</td><td>Tout le personnel : fiches, comptes, identifiants, départs</td></tr>
                <tr><td>Personnel</td><td>4</td><td>Déclarer et consulter ses activités, voir l'organisation</td></tr>
            </table>
        </div>
    </main>

</body>
</html>