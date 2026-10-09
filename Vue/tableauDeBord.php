<?php
require __DIR__ . '/../Model/verifierconnexion.php';
require __DIR__ . '/../Model/tableaudebord.php';

$totalActivites = array_sum(array_column($activitesParCategorie, 'nombre'));
$maxStatut = max(array_column($effectifsParStatut, 'nombre') ?: [1]);
$maxCategorie = max(array_column($activitesParCategorie, 'nombre') ?: [1]);

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
<body>
    <?php include 'navbar.php'; ?>

    <main class="page-contenu bord">
        <header class="bord-entete">
            <p class="bord-surtitre">Tableau de bord</p>
            <h1><?= htmlspecialchars($nomPerimetre) ?></h1>
            <p class="bord-periode">Du <?= date('d/m/Y', strtotime($dateDebut)) ?> au <?= date('d/m/Y', strtotime($dateFin)) ?></p>
        </header>

        <form method="get" action="tableauDeBord.php" class="bord-filtres">
            <div class="bord-champ">
                <label for="perimetre">Périmètre</label>
                <select id="perimetre" name="perimetre">
                    <option value="laboratoire" <?= $perimetre === 'laboratoire' ? 'selected' : '' ?>>Laboratoire</option>
                    <option value="departement" <?= $perimetre === 'departement' ? 'selected' : '' ?>>Département</option>
                    <option value="groupe" <?= $perimetre === 'groupe' ? 'selected' : '' ?>>Groupe</option>
                </select>
            </div>

            <div class="bord-champ">
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
            </div>

            <div class="bord-champ">
                <label for="debut">Du</label>
                <input type="date" id="debut" name="debut" value="<?= htmlspecialchars($dateDebut) ?>" required>
            </div>

            <div class="bord-champ">
                <label for="fin">Au</label>
                <input type="date" id="fin" name="fin" value="<?= htmlspecialchars($dateFin) ?>" required>
            </div>

            <button type="submit">Afficher</button>
        </form>

        <section class="bord-chiffres">
            <div class="bord-carte">
                <span class="bord-carte-libelle">Personnes</span>
                <span class="bord-carte-chiffre"><?= $effectifTotal ?></span>
                <span class="bord-carte-note">rattachées sur la période</span>
            </div>
            <div class="bord-carte bord-carte-entree">
                <span class="bord-carte-libelle">Entrées</span>
                <span class="bord-carte-chiffre"><?= $nombreEntrees > 0 ? "+" . $nombreEntrees : 0 ?></span>
                <span class="bord-carte-note">arrivées sur la période</span>
            </div>
            <div class="bord-carte bord-carte-sortie">
                <span class="bord-carte-libelle">Sorties</span>
                <span class="bord-carte-chiffre"><?= $nombreSorties > 0 ? "−" . $nombreSorties : 0 ?></span>
                <span class="bord-carte-note">départs sur la période</span>
            </div>
            <div class="bord-carte">
                <span class="bord-carte-libelle">Activités</span>
                <span class="bord-carte-chiffre"><?= $totalActivites ?></span>
                <span class="bord-carte-note">en cours sur la période</span>
            </div>
        </section>

        <div class="bord-grille">
            <section class="bord-panneau">
                <h2>Effectifs par statut</h2>
                <?php if (empty($effectifsParStatut)): ?>
                    <p class="bord-vide">Aucune donnée sur cette période.</p>
                <?php else: ?>
                    <?php foreach ($effectifsParStatut as $ligne): ?>
                        <div class="bord-barre">
                            <div class="bord-barre-texte">
                                <span><?= htmlspecialchars($ligne['statut']) ?></span>
                                <strong><?= $ligne['nombre'] ?></strong>
                            </div>
                            <div class="bord-barre-piste">
                                <div class="bord-barre-remplie" style="width: <?= round($ligne['nombre'] / $maxStatut * 100) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <section class="bord-panneau">
                <h2>Activités par catégorie</h2>
                <?php if (empty($activitesParCategorie)): ?>
                    <p class="bord-vide">Aucune activité sur cette période.</p>
                <?php else: ?>
                    <?php foreach ($activitesParCategorie as $ligne): ?>
                        <div class="bord-barre">
                            <div class="bord-barre-texte">
                                <span><?= htmlspecialchars($ligne['categorie']) ?></span>
                                <strong><?= $ligne['nombre'] ?></strong>
                            </div>
                            <div class="bord-barre-piste">
                                <div class="bord-barre-remplie bord-barre-activite" style="width: <?= round($ligne['nombre'] / $maxCategorie * 100) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>

        <section class="bord-panneau">
            <h2>Personnes du périmètre</h2>
            <?php if (empty($personnes)): ?>
                <p class="bord-vide">Personne sur cette période.</p>
            <?php else: ?>
                <div class="tableau-defilant">
                    <table class="bord-tableau">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Arrivée</th>
                                <th>Départ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personnes as $personne): ?>
                                <tr>
                                    <td><?= htmlspecialchars($personne['nom']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($personne['date_arrivee'])) ?></td>
                                    <td>
                                        <?php if ($personne['date_depart']): ?>
                                            <span class="bord-etat bord-etat-parti">Parti le <?= date('d/m/Y', strtotime($personne['date_depart'])) ?></span>
                                        <?php else: ?>
                                            <span class="bord-etat">Présent</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>