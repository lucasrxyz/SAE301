<?php
    // Vérifie que l'utilisateur est connecté
    require __DIR__ . '/../Model/verifierconnexion.php';
    // Traite le formulaire et prépare la liste
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

    <?php // Barre de navigation, commune à toutes les pages ?>
    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="page-contenu formulaire-page">
        <section class="formulaire-carte">
            <p class="formulaire-surtitre">Mes activités</p>
            <h1>Déclarer une activité<?= $mode !== '' ? ' ' . $mode : '' ?></h1>

            <?php // Messages d'erreur et de réussite ?>
            <?php if ($erreurActivite !== ''): ?>
                <p class="message-erreur"><?= htmlspecialchars($erreurActivite) ?></p>
            <?php endif; ?>

            <?php if ($activiteEnregistree): ?>
                <p class="message-succes">L'activité a été enregistrée.</p>
            <?php endif; ?>

            <?php // Choix : individuelle ou collective ?>
            <form action="declarerActivite.php" method="post" class="formulaire-vertical">
                <p class="aide">Cette activité est-elle individuelle ou collective ?</p>
                <button type="submit" name="mode" value="individuelle" <?= $mode !== 'individuelle' ? 'class="bouton-secondaire"' : '' ?>>Individuelle</button>
                <button type="submit" name="mode" value="collective" <?= $mode !== 'collective' ? 'class="bouton-secondaire"' : '' ?>>Collective</button>
            </form>

            <?php // Le formulaire s'affiche une fois le choix fait ?>
            <?php if ($mode !== ''): ?>
            <form action="declarerActivite.php" method="post" class="formulaire-vertical">
                <input type="hidden" name="mode" value="<?= $mode ?>">

                <label for="id_type">Type d'activité</label>
                <select name="id_type" id="id_type" required>
                    <option value="">-- Choisir un type --</option>
                    <?php foreach ($tousLesTypes as $type): ?>
                        <option value="<?= $type['id_type'] ?>" <?= $typeactivite == $type['id_type'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['categorie'] . ' - ' . $type['nom_type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php // Titre, dates et description ?>
                <label for="titre">Titre de l'activité</label>
                <input type="text" name="titre" id="titre" value="<?= htmlspecialchars($titre) ?>" required>

                <label for="date_debut">Début</label>
                <input type="datetime-local" name="date_debut" id="date_debut" value="<?= htmlspecialchars($datedebut) ?>" required>

                <label for="date_fin">Fin</label>
                <input type="datetime-local" name="date_fin" id="date_fin" value="<?= htmlspecialchars($datefin) ?>">
                <p class="aide">Laisser vide si l'activité n'a pas de durée (relecture, jury…).</p>

                <label for="description">Description (revue, lieu, organisme…)</label>
                <textarea name="description" id="description" rows="3"><?= htmlspecialchars($description) ?></textarea>

                <?php // Liste des collaborateurs, seulement en mode collectif ?>
                <?php if ($mode === 'collective'): ?>
                    <?php // Les collaborateurs déjà cochés restent affichés, même si les filtres changent ?>
                    <p class="formulaire-surtitre">Collaborateurs choisis (<?= count($collaborateurs) ?>)</p>
                    <div class="liste-collaborateurs">
                        <?php foreach ($listePersonnels as $personnel): ?>
                            <?php if (in_array($personnel['id'], $collaborateurs)): ?>
                                <label class="case-a-cocher">
                                    <input type="checkbox" name="collaborateurs[]" value="<?= $personnel['id'] ?>" checked>
                                    <?= htmlspecialchars($personnel['nom'] . ' ' . $personnel['prenom'] . ' [idhal: ' . $personnel['id_idhal'] . '] [orcid: ' . $personnel['id_orcid'] . ']') ?>
                                </label>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <?php // Filtres : le bouton recharge la page sans rien enregistrer ?>
                    <p class="formulaire-surtitre">Rechercher des collaborateurs</p>
                    <label for="filtre_nom">Nom ou prénom</label>
                    <input type="text" name="filtre_nom" id="filtre_nom" value="<?= htmlspecialchars($filtreNom) ?>">

                    <label for="filtre_departement">Département</label>
                    <select name="filtre_departement" id="filtre_departement">
                        <option value="">Tous</option>
                        <?php foreach ($departementsFiltre as $departement): ?>
                            <option value="<?= $departement['id_departement'] ?>" <?= $filtreDepartement == $departement['id_departement'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($departement['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="filtre_groupe">Groupe</label>
                    <select name="filtre_groupe" id="filtre_groupe">
                        <option value="">Tous</option>
                        <?php foreach ($groupesFiltre as $groupe): ?>
                            <option value="<?= $groupe['id_groupe'] ?>" <?= $filtreGroupe == $groupe['id_groupe'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($groupe['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" name="filtrer" formnovalidate class="bouton-secondaire">Filtrer</button>

                    <?php // Résultats des filtres, sans ceux qui sont déjà cochés ?>
                    <p class="aide"><?= count($collaborateursFiltres) ?> personne(s) trouvée(s). Cochez puis cliquez sur Enregistrer.</p>
                    <div class="liste-collaborateurs">
                        <?php foreach ($collaborateursFiltres as $personnel): ?>
                            <?php if (!in_array($personnel['id'], $collaborateurs)): ?>
                                <label class="case-a-cocher">
                                    <input type="checkbox" name="collaborateurs[]" value="<?= $personnel['id'] ?>">
                                    <?= htmlspecialchars($personnel['nom'] . ' ' . $personnel['prenom'] . ' [idhal: ' . $personnel['id_idhal'] . '] [orcid: ' . $personnel['id_orcid'] . ']') ?>
                                </label>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <label class="case-a-cocher">
                    <input type="checkbox" name="en_cours" <?= $enCours ? 'checked' : '' ?>>
                    Activité toujours en cours
                </label>

                <button type="submit" name="enregistrer">Enregistrer l'activité</button>
            </form>
            <?php endif; ?>
        </section>

        <?php // Liste de mes activités ?>
        <section class="liste-activites">
            <h2>Mes activités déclarées</h2>

            <?php // Recherche et filtres ?>
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

            <?php // Tableau de mes activités, avec des titres de colonnes cliquables pour trier ?>
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
                            <th>Rôle</th>
                            <th>Détails</th>
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
                                <td><?= formaterDate($activite['date_debut']) ?></td>
                                <td><?= $activite['date_fin'] === null ? '—' : formaterDate($activite['date_fin']) ?></td>
                                <td><?= htmlspecialchars($activite['role'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($activite['description'] ?? '') ?></td>
                                <td>
                                    <?php // Statut : en cours ou terminée ?>
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
            <?php // Message s'il n'y a aucune activité ?>
            <?php elseif ($filtreActif): ?>
                <p>Aucune activité ne correspond à votre recherche.</p>
            <?php else: ?>
                <p>Vous n'avez encore déclaré aucune activité.</p>
            <?php endif; ?>
        </section>
    </main>

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