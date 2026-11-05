<?php
    // Sections du rapport : titre => catégorie de TypeActivite ('' si aucune donnée en base)
    $sections = [
        '1.3 Enseignement' => 'Enseignement',
        '1.4 Recherche' => 'Contrat',
        '2.1 Thématiques' => '',
        '2.2 Publications' => 'Publication',
        '2.3 Encadrement doctoral' => 'Encadrement doctoral',
        '2.4 Rayonnement' => 'Rayonnement',
        '3 Pédagogie' => '',
        '5 Production scientifique' => '',
        '6 Conditions' => '',
        '7 Titres' => ''
    ];
?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport d'activités</title>
    <style>
        body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #222222; }
        h1 { font-size: 20pt; color: #1F3864; text-align: center; margin-bottom: 4pt; }
        p.sous-titre { text-align: center; font-size: 12pt; color: #555555; margin-top: 0; }
        h2 { font-size: 14pt; color: #1F3864; border-bottom: 1.5pt solid #1F3864; padding-bottom: 2pt; margin-top: 20pt; }
        h3 { font-size: 12pt; color: #2E5395; margin-top: 12pt; }
        table { border-collapse: collapse; width: 100%; margin-top: 6pt; }
        th, td { border: 1pt solid #A6A6A6; padding: 4pt 6pt; vertical-align: top; font-size: 10pt; }
        th { background: #D9E2F3; text-align: left; }
        p.vide { font-style: italic; color: #7F7F7F; }
        p.pied { font-size: 9pt; color: #7F7F7F; text-align: center; margin-top: 30pt; }
    </style>
</head>
<body>

    <h1>Rapport d'activités individuel</h1>
    <p class="sous-titre">
        <?= htmlspecialchars($personnel['prenom']) ?> <?= htmlspecialchars($personnel['nom']) ?>
        - période du <?= date('d/m/Y H:i', strtotime($dateDebut)) ?> au <?= date('d/m/Y H:i', strtotime($dateFin)) ?>
    </p>

    <h2>1.1 Fiche d'identité</h2>
    <table>
        <tr><th>Nom</th><td><?= htmlspecialchars($personnel['nom']) ?></td></tr>
        <tr><th>Prénom</th><td><?= htmlspecialchars($personnel['prenom']) ?></td></tr>
        <tr><th>Mail</th><td><?= htmlspecialchars($personnel['mail']) ?></td></tr>
        <tr><th>ORCID</th><td><?= htmlspecialchars($personnel['id_orcid'] ?? 'Non renseigné') ?></td></tr>
        <tr><th>IdHAL</th><td><?= htmlspecialchars($personnel['id_idhal'] ?? 'Non renseigné') ?></td></tr>
        <tr><th>Date d'arrivée</th><td><?= date('d/m/Y', strtotime($personnel['date_arrivee'])) ?></td></tr>
    </table>

    <h2>1.2 Positions</h2>
    <h3>Statuts</h3>
    <?php if ($statuts): ?>
        <table>
            <tr>
                <th>Statut</th>
                <th>Quotité recherche</th>
                <th>Du</th>
                <th>Au</th>
            </tr>
            <?php foreach ($statuts as $statut): ?>
                <tr>
                    <td><?= htmlspecialchars($statut['statut']) ?></td>
                    <td><?= htmlspecialchars($statut['quotite_recherche']) ?> %</td>
                    <td><?= date('d/m/Y', strtotime($statut['date_debut'])) ?></td>
                    <td><?= $statut['date_fin'] === null ? 'En cours' : date('d/m/Y', strtotime($statut['date_fin'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p class="vide">Aucune donnée sur la période.</p>
    <?php endif; ?>

    <h3>Rattachements</h3>
    <?php if ($rattachements): ?>
        <table>
            <tr>
                <th>Groupe / département</th>
                <th>Type de rattachement</th>
                <th>Responsable</th>
                <th>Du</th>
                <th>Au</th>
            </tr>
            <?php foreach ($rattachements as $rattachement): ?>
                <tr>
                    <td><?= htmlspecialchars($rattachement['type_affectation'] . ' - ' . $rattachement['nom_affectation']) ?></td>
                    <td><?= htmlspecialchars($rattachement['type_rattachement']) ?></td>
                    <td><?= $rattachement['responsable_groupe'] ? 'Oui' : 'Non' ?></td>
                    <td><?= date('d/m/Y', strtotime($rattachement['date_debut'])) ?></td>
                    <td><?= $rattachement['date_fin'] === null ? 'En cours' : date('d/m/Y', strtotime($rattachement['date_fin'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p class="vide">Aucune donnée sur la période.</p>
    <?php endif; ?>

    <?php foreach ($sections as $titreSection => $categorie): ?>
        <h2><?= htmlspecialchars($titreSection) ?></h2>
        <?php if ($categorie !== '' && isset($activitesParCategorie[$categorie])): ?>
            <table>
                <tr>
                    <th>Type</th>
                    <th>Titre</th>
                    <th>Rôle</th>
                    <th>Du</th>
                    <th>Au</th>
                </tr>
                <?php foreach ($activitesParCategorie[$categorie] as $activite): ?>
                    <tr>
                        <td><?= htmlspecialchars($activite['nom_type']) ?></td>
                        <td><?= htmlspecialchars($activite['titre']) ?></td>
                        <td><?= htmlspecialchars($activite['role'] ?? '—') ?></td>
                        <td><?= date('d/m/Y', strtotime($activite['date_debut'])) ?></td>
                        <td><?= $activite['date_fin'] === null ? 'En cours' : date('d/m/Y', strtotime($activite['date_fin'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p class="vide">Aucune donnée sur la période.</p>
        <?php endif; ?>
    <?php endforeach; ?>

    <p class="pied">Rapport généré le <?= date('d/m/Y') ?> — document modifiable</p>

</body>
</html>