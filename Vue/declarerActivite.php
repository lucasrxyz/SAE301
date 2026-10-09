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

            <form method="get" action="declarerActivite.php" class="filtres-activites">
                <div class="bord-champ champ-recherche">
                    <label for="recherche">Rechercher</label>
                    <input type="text" id="recherche" name="recherche" placeholder="Titre de l'activité" value="<?= htmlspecialchars($recherche) ?>">
                </div>

                <div class="bord-champ">
                    <label for="type">Type</label>
                    <select id="type" name="type">
                        <option value="">Tous</option>
                        <?php foreach ($tousLesTypes as $type): ?>
                            <option value="<?= $type['id_type'] ?>" <?= $filtreType == $type['id_type'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type['nom_type']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="bord-champ">
                    <label for="etat">Statut</label>
                    <select id="etat" name="etat">
                        <option value="">Tous</option>
                        <option value="en_cours" <?= $filtreEtat === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                        <option value="terminee" <?= $filtreEtat === 'terminee' ? 'selected' : '' ?>>Terminée</option>
                    </select>
                </div>

                <div class="bord-champ">
                    <label for="debut">Du</label>
                    <input type="date" id="debut" name="debut" value="<?= htmlspecialchars($filtreDebut) ?>">
                </div>

                <div class="bord-champ">
                    <label for="fin">Au</label>
                    <input type="date" id="fin" name="fin" value="<?= htmlspecialchars($filtreFin) ?>">
                </div>

                <input type="hidden" name="tri" value="<?= htmlspecialchars($tri) ?>">
                <input type="hidden" name="ordre" value="<?= htmlspecialchars($ordre) ?>">

                <div class="filtres-boutons">
                    <button type="submit">Filtrer</button>
                    <?php if ($filtreActif): ?>
                        <a href="declarerActivite.php" class="lien-effacer">Effacer</a>
                    <?php endif; ?>
                </div>
            </form>

            <?php if ($mesActivites): ?>
                <p class="aide nombre-resultats"><?= count($mesActivites) ?> activité(s)</p>
                <div class="tableau-defilant">
                    <table>
                        <tr>
                            <th>Catégorie</th>
                            <th>
                                <a class="lien-tri" href="?<?= $filtresUrl ?>&tri=type&ordre=<?= $tri === 'type' ? $ordreInverse : 'asc' ?>">
                                    Type <?= $tri === 'type' ? ($ordre === 'asc' ? '▲' : '▼') : '' ?>
                                </a>
                            </th>
                            <th>
                                <a class="lien-tri" href="?<?= $filtresUrl ?>&tri=titre&ordre=<?= $tri === 'titre' ? $ordreInverse : 'asc' ?>">
                                    Titre <?= $tri === 'titre' ? ($ordre === 'asc' ? '▲' : '▼') : '' ?>
                                </a>
                            </th>
                            <th>
                                <a class="lien-tri" href="?<?= $filtresUrl ?>&tri=date&ordre=<?= $tri === 'date' ? $ordreInverse : 'desc' ?>">
                                    Du <?= $tri === 'date' ? ($ordre === 'asc' ? '▲' : '▼') : '' ?>
                                </a>
                            </th>
                            <th>Au</th>
                            <th>
                                <a class="lien-tri" href="?<?= $filtresUrl ?>&tri=etat&ordre=<?= $tri === 'etat' ? $ordreInverse : 'desc' ?>">
                                    Statut <?= $tri === 'etat' ? ($ordre === 'asc' ? '▲' : '▼') : '' ?>
                                </a>
                            </th>
                        </tr>
                        <?php foreach ($mesActivites as $activite): ?>
                            <tr>
                                <td><?= htmlspecialchars($activite['categorie']) ?></td>
                                <td><?= htmlspecialchars($activite['nom_type']) ?></td>
                                <td><?= htmlspecialchars($activite['titre']) ?></td>
                                <td><?= date('d/m/Y', strtotime($activite['date_debut'])) ?></td>
                                <td><?= $activite['date_fin'] === null ? '—' : date('d/m/Y', strtotime($activite['date_fin'])) ?></td>
                                <td>
                                    <?php if ($activite['date_fin'] === null || $activite['date_fin'] >= date('Y-m-d')): ?>
                                        <span class="bord-etat">En cours</span>
                                    <?php else: ?>
                                        <span class="bord-etat bord-etat-parti">Terminée</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php elseif ($filtreActif): ?>
                <p>Aucune activité ne correspond à votre recherche.</p>
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