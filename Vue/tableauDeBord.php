<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/tableaudebord.php';

    $totalActivites = array_sum(array_column($activitesParCategorie, 'nombre'));

    $nomPerimetre = 'Tout le laboratoire';
    foreach ($departements as $departement) {
        if ($perimetre === 'departement' && $idChoisi == $departement['id_departement']) {
            $nomPerimetre = 'Département ' . $departement['nom'];
        }
    }
    foreach ($groupes as $groupe) {
        if ($perimetre === 'groupe' && $idChoisi == $groupe['id_groupe']) {
            $nomPerimetre = 'Groupe ' . $groupe['nom'];
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tableau de bord</title>
        <link rel="stylesheet" href="style.css">
    </head>
        <footer class="footer">
            <p>&copy; <?= date('Y') ?> K.A.B.L Solution Hauts-de-France - Tous droits réservés</p>
            <ul class="footer-liens">
                <li><a href="#">Mentions légales</a></li>
                <li><a href="#">Contact</a></li>
                <li><a href="#">Accessibilité</a></li>
            </ul>
        </footer>
    <body>
        <?php include 'navbar.php'; ?>

        <main class="page-contenu">
            <h1>Tableau de bord</h1>
            <p><?= htmlspecialchars($nomPerimetre) ?>, du <?= date('d/m/Y', strtotime($dateDebut)) ?> au <?= date('d/m/Y', strtotime($dateFin)) ?></p>

            <form method="get" action="tableauDeBord.php">
                <label for="perimetre">Périmètre</label>
                <select id="perimetre" name="perimetre">
                    <option value="laboratoire" <?= $perimetre === 'laboratoire' ? 'selected' : '' ?>>Laboratoire</option>
                    <option value="departement" <?= $perimetre === 'departement' ? 'selected' : '' ?>>Département</option>
                    <option value="groupe" <?= $perimetre === 'groupe' ? 'selected' : '' ?>>Groupe</option>
                </select>

                <label for="id">Lequel</label>
                <select id="id" name="id">
                    <option value="">Tout le laboratoire</option>
                    <optgroup label="Départements">
                        <?php foreach ($departements as $departement): ?>
                            <option value="<?= $departement['id_departement'] ?>" <?= $perimetre === 'departement' && $idChoisi == $departement['id_departement'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($departement['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Groupes">
                        <?php foreach ($groupes as $groupe): ?>
                            <option value="<?= $groupe['id_groupe'] ?>" <?= $perimetre === 'groupe' && $idChoisi == $groupe['id_groupe'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($groupe['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>

                <label for="debut">Du</label>
                <input type="date" id="debut" name="debut" value="<?= htmlspecialchars($dateDebut) ?>" required>

                <label for="fin">Au</label>
                <input type="date" id="fin" name="fin" value="<?= htmlspecialchars($dateFin) ?>" required>

                <button type="submit">Afficher</button>
            </form>

            <div class="chiffres">
                <div class="chiffre">
                    <strong><?= $effectifTotal ?></strong>
                    <span>Personnes</span>
                </div>
                <div class="chiffre">
                    <strong><?= $nombreEntrees ?></strong>
                    <span>Entrées</span>
                </div>
                <div class="chiffre">
                    <strong><?= $nombreSorties ?></strong>
                    <span>Sorties</span>
                </div>
                <div class="chiffre">
                    <strong><?= $totalActivites ?></strong>
                    <span>Activités</span>
                </div>
            </div>

            <div class="deux-colonnes">
                <section>
                    <h2>Effectifs par statut</h2>
                    <?php if ($effectifsParStatut): ?>
                        <div class="tableau-defilant">
                            <table>
                                <tr>
                                    <th>Statut</th>
                                    <th>Nombre</th>
                                    <th class="colonne-barre"></th>
                                </tr>
                                <?php foreach ($effectifsParStatut as $ligne): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($ligne['statut']) ?></td>
                                        <td><?= $ligne['nombre'] ?></td>
                                        <td><div class="fond-barre"><div class="barre" style="width: <?= round($ligne['nombre'] / $effectifTotal * 100) ?>%"></div></div></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Aucune donnée sur cette période.</p>
                    <?php endif; ?>
                </section>

                <section>
                    <h2>Activités par catégorie</h2>
                    <?php if ($activitesParCategorie): ?>
                        <div class="tableau-defilant">
                            <table>
                                <tr>
                                    <th>Catégorie</th>
                                    <th>Nombre</th>
                                    <th class="colonne-barre"></th>
                                </tr>
                                <?php foreach ($activitesParCategorie as $ligne): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($ligne['categorie']) ?></td>
                                        <td><?= $ligne['nombre'] ?></td>
                                        <td><div class="fond-barre"><div class="barre" style="width: <?= round($ligne['nombre'] / $totalActivites * 100) ?>%"></div></div></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Aucune activité sur cette période.</p>
                    <?php endif; ?>
                </section>
            </div>

            <h2>Personnes du périmètre</h2>
            <?php if ($personnes): ?>
                <div class="tableau-defilant">
                    <table>
                        <tr>
                            <th>Nom</th>
                            <th>Arrivée</th>
                            <th>Départ</th>
                        </tr>
                        <?php foreach ($personnes as $personne): ?>
                            <tr>
                                <td><?= htmlspecialchars($personne['nom']) ?></td>
                                <td><?= date('d/m/Y', strtotime($personne['date_arrivee'])) ?></td>
                                <td><?= $personne['date_depart'] ? date('d/m/Y', strtotime($personne['date_depart'])) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php else: ?>
                <p>Personne sur cette période.</p>
            <?php endif; ?>
        </main>
    </body>
    </html>