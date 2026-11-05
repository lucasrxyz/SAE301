<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/confirmerParticipation.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes participations</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require __DIR__ . '/navbar.php'; ?>

    <main class="page-contenu formulaire-page">
        <section class="liste-activites">
            <p class="formulaire-surtitre">Activités collectives</p>
            <h1>Mes participations</h1>

            <?php if ($reponse === 'confirmee'): ?>
                <p class="message-succes">Votre participation est confirmée.</p>
            <?php elseif ($reponse === 'refusee'): ?>
                <p class="message-succes">Votre participation est refusée. Elle ne sera pas comptée dans les bilans.</p>
            <?php endif; ?>

            <?php if ($participations): ?>
                <div class="tableau-defilant">
                    <table>
                        <tr>
                            <th>Statut</th>
                            <th>Activité</th>
                            <th>Type</th>
                            <th>Porteur</th>
                            <th>Mon rôle</th>
                            <th>Du</th>
                            <th>Au</th>
                        </tr>
                        <?php foreach ($participations as $participation): ?>
                            <tr>
                                <td>
                                    <?php if ($participation['confirme'] === null): ?>
                                        <form action="" method="post">
                                            <input type="hidden" name="id_activite" value="<?= $participation['id_activite'] ?>">
                                            <button type="submit" name="confirmer">Confirmer</button>
                                            <button type="submit" name="refuser" class="bouton-secondaire">Refuser</button>
                                        </form>
                                    <?php elseif ($participation['confirme']): ?>
                                        <span class="bord-etat">Confirmée</span>
                                    <?php else: ?>
                                        <span class="bord-etat bord-etat-parti">Refusée</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($participation['titre']) ?></td>
                                <td><?= htmlspecialchars($participation['nom_type']) ?></td>
                                <td><?= htmlspecialchars($participation['prenom'] . ' ' . $participation['nom']) ?></td>
                                <td><?= htmlspecialchars($participation['role'] ?? '—') ?></td>
                                <td><?= formaterDate($participation['date_debut']) ?></td>
                                <td><?= $participation['date_fin'] === null ? '—' : formaterDate($participation['date_fin']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php else: ?>
                <p>Vous n'êtes participant d'aucune activité collective.</p>
            <?php endif; ?>

            <h2>Réponses à mes activités</h2>
            <?php if ($reponsesParticipants): ?>
                <div class="tableau-defilant">
                    <table>
                        <tr>
                            <th>Statut</th>
                            <th>Activité</th>
                            <th>Participant</th>
                            <th>Rôle</th>
                        </tr>
                        <?php foreach ($reponsesParticipants as $participant): ?>
                            <tr>
                                <td>
                                    <?php if ($participant['confirme'] === null): ?>
                                        <span class="bord-etat">En attente</span>
                                    <?php elseif ($participant['confirme']): ?>
                                        <span class="bord-etat">Confirmée</span>
                                    <?php else: ?>
                                        <span class="bord-etat bord-etat-parti">Refusée</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($participant['titre']) ?></td>
                                <td><?= htmlspecialchars($participant['prenom'] . ' ' . $participant['nom']) ?></td>
                                <td><?= htmlspecialchars($participant['role'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php else: ?>
                <p>Aucun participant sur vos activités collectives.</p>
            <?php endif; ?>
        </section>
    </main>

</body>
</html>
