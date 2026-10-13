<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    exigerDroit('voir_personnel');
    require __DIR__ . '/../Model/desactiverpersonnel.php';
    require __DIR__ . '/../Model/modifieridentite.php';
    require __DIR__ . '/../Model/cloturerrattachement.php';
    require __DIR__ . '/../Model/changerstatut.php';
    require __DIR__ . '/../Model/nommerresponsable.php';
    require __DIR__ . '/../Model/fichepersonnel.php';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Fiche personnel</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>

    <?php require __DIR__ . '/navbar.php'; ?>

    <div class="page-contenu">

        <?php if ($personnelIntrouvable): ?>
            <p class="message-erreur">Personnel introuvable.</p>
        <?php endif; ?>

        <?php if ($personnelDesactive): ?>
            <p class="message-succes">Le personnel a été désactivé.</p>
        <?php endif; ?>

        <?php if ($erreurDesactivation): ?>
            <p class="message-erreur"><?= htmlspecialchars($erreurDesactivation) ?></p>
        <?php endif; ?>

        <?php if ($messageIdentite): ?>
            <p class="message-succes"><?= htmlspecialchars($messageIdentite) ?></p>
        <?php endif; ?>

        <?php if ($erreurIdentite): ?>
            <p class="message-erreur"><?= htmlspecialchars($erreurIdentite) ?></p>
        <?php endif; ?>

        <?php if ($rattachementCloture): ?>
            <p class="message-succes">Le rattachement a été clôturé.</p>
        <?php endif; ?>

        <?php if ($erreurClotureRattachement !== ''): ?>
            <p class="message-erreur"><?= htmlspecialchars($erreurClotureRattachement) ?></p>
        <?php endif; ?>

        <?php if ($statutChange): ?>
            <p class="message-succes">Le nouveau statut a été enregistré.</p>
        <?php endif; ?>

        <?php if ($erreurStatut !== ''): ?>
            <p class="message-erreur"><?= htmlspecialchars($erreurStatut) ?></p>
        <?php endif; ?>

        <?php if ($responsableNomme): ?>
            <p class="message-succes">La nomination du responsable a été enregistrée.</p>
        <?php endif; ?>

        <?php if ($erreurResponsable !== ''): ?>
            <p class="message-erreur"><?= htmlspecialchars($erreurResponsable) ?></p>
        <?php endif; ?>

        <?php if ($personnel): ?>
            <h1>Fiche de <?= htmlspecialchars($personnel['prenom']) ?> <?= htmlspecialchars($personnel['nom']) ?></h1>

            <?php if ($estRH): ?>
                <form method="get" action="">
                    <input type="hidden" name="id" value="<?= htmlspecialchars((string) $personnel['id']) ?>">
                    <button type="submit" formaction="modifierPersonnel.php">Modifier les coordonnées</button>
                    <button type="submit" formaction="ajouterRattachement.php">Ajouter un rattachement</button>
                </form>
            <?php endif; ?>

            <div class="tableau-defilant">
                <table id="detail-table">
                    <tr>
                        <th>Nom</th>
                        <td><?= htmlspecialchars($personnel['nom']) ?></td>
                    </tr>
                    <tr>
                        <th>Prénom</th>
                        <td><?= htmlspecialchars($personnel['prenom']) ?></td>
                    </tr>
                    <tr>
                        <th>Mail</th>
                        <td><?= htmlspecialchars($personnel['mail']) ?></td>
                    </tr>
                    <tr>
                        <th>ORCID</th>
                        <td><?= htmlspecialchars($personnel['id_orcid'] ?? 'Non renseigné') ?></td>
                    </tr>
                    <tr>
                        <th>IdHAL</th>
                        <td><?= htmlspecialchars($personnel['id_idhal'] ?? 'Non renseigné') ?></td>
                    </tr>
                    <tr>
                        <th>Date d'arrivée</th>
                        <td><?= date('d/m/Y', strtotime($personnel['date_arrivee'])) ?></td>
                    </tr>
                    <tr>
                        <th>État</th>
                        <td><?= $personnel['date_depart'] === null ? 'Actif' : 'Parti le ' . date('d/m/Y', strtotime($personnel['date_depart'])) ?></td>
                    </tr>
                </table>
            </div>

            <?php if ($anciensNoms): ?>
                <h2>Anciens noms</h2>
                <div class="tableau-defilant">
                    <table>
                        <tr>
                            <th>Ancien nom</th>
                            <th>Jusqu'au</th>
                        </tr>
                        <?php foreach ($anciensNoms as $ancien): ?>
                            <tr>
                                <td><?= htmlspecialchars($ancien['ancien_nom']) ?></td>
                                <td><?= date('d/m/Y', strtotime($ancien['date_changement'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>

            <h2>Historique des statuts</h2>
            <?php if ($historiqueStatuts): ?>
                <div class="tableau-defilant">
                    <table>
                        <tr>
                            <th>Statut</th>
                            <th>Quotité recherche</th>
                            <th>Du</th>
                            <th>Au</th>
                        </tr>
                        <?php foreach ($historiqueStatuts as $statut): ?>
                            <tr>
                                <td><?= htmlspecialchars($statut['statut']) ?></td>
                                <td><?= round($statut['quotite_recherche'] * 100) ?> %</td>
                                <td><?= date('d/m/Y', strtotime($statut['date_debut'])) ?></td>
                                <td><?= $statut['date_fin'] === null ? 'En cours' : date('d/m/Y', strtotime($statut['date_fin'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php else: ?>
                <p>Aucun statut enregistré.</p>
            <?php endif; ?>

            <?php if ($estRH && $personnel['date_depart'] === null): ?>
                <form method="post" action="">
                    <input type="hidden" name="id_personnel" value="<?= $personnel['id'] ?>">

                    <label for="nouveau_statut">Nouveau statut</label>
                    <select name="nouveau_statut" id="nouveau_statut" required>
                        <?php foreach ($statutsPossibles as $statutPossible): ?>
                            <option value="<?= htmlspecialchars($statutPossible) ?>"><?= htmlspecialchars($statutPossible) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="nouvelle_quotite">Quotité (%)</label>
                    <input type="number" name="nouvelle_quotite" id="nouvelle_quotite" min="0" max="100" required>

                    <label for="date_effet">À partir du</label>
                    <input type="date" name="date_effet" id="date_effet" value="<?= date('Y-m-d') ?>" required>

                    <button type="submit" name="changer_statut">Changer le statut</button>
                </form>
            <?php endif; ?>

            <h2>Historique des rattachements</h2>
            <?php if ($historiqueRattachements): ?>
                <div class="tableau-defilant">
                    <table>
                        <tr>
                            <th>Groupe / département</th>
                            <th>Type de rattachement</th>
                            <th>Responsable</th>
                            <th>Du</th>
                            <th>Au</th>
                            <?php if ($estRH): ?>
                                <th>Action</th>
                            <?php endif; ?>
                        </tr>
                        <?php foreach ($historiqueRattachements as $rattachement): ?>
                            <tr>
                                <td><?= htmlspecialchars($rattachement['type_affectation'] . ' - ' . $rattachement['affectation']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($rattachement['type_rattachement'])) ?></td>
                                <td><?= $rattachement['responsable_groupe'] ? 'Oui' : 'Non' ?></td>
                                <td><?= date('d/m/Y', strtotime($rattachement['date_debut'])) ?></td>
                                <td><?= $rattachement['date_fin'] === null ? 'En cours' : date('d/m/Y', strtotime($rattachement['date_fin'])) ?></td>
                                <?php if ($estRH): ?>
                                    <td>
                                        <?php if ($rattachement['date_fin'] === null): ?>
                                            <?php
                                                $dateFinAffichee = (string) $idRattachementCloture === (string) $rattachement['id'] && is_string($dateFinClotureSaisie)
                                                    ? $dateFinClotureSaisie
                                                    : max(date('Y-m-d'), $rattachement['date_debut']);
                                            ?>
                                            <form method="post" action="ficheDetailleePersonnel.php?id=<?= urlencode((string) $personnel['id']) ?>">
                                                <input type="hidden" name="id_personnel" value="<?= htmlspecialchars((string) $personnel['id']) ?>">
                                                <input type="hidden" name="id_rattachement" value="<?= htmlspecialchars((string) $rattachement['id']) ?>">
                                                <label for="date_fin_rattachement_<?= htmlspecialchars((string) $rattachement['id']) ?>">Date de fin</label>
                                                <input
                                                    type="date"
                                                    name="date_fin_rattachement"
                                                    id="date_fin_rattachement_<?= htmlspecialchars((string) $rattachement['id']) ?>"
                                                    value="<?= htmlspecialchars($dateFinAffichee) ?>"
                                                    min="<?= htmlspecialchars($rattachement['date_debut']) ?>"
                                                    required
                                                >
                                                <button type="submit" name="cloturer_rattachement" value="true">Clôturer</button>
                                            </form>
                                            <?php if (!$rattachement['responsable_groupe']): ?>
                                                <form method="post" action="ficheDetailleePersonnel.php?id=<?= $personnel['id'] ?>">
                                                    <input type="hidden" name="id_personnel" value="<?= $personnel['id'] ?>">
                                                    <input type="hidden" name="id_rattachement" value="<?= $rattachement['id'] ?>">
                                                    <label for="date_nomination_<?= $rattachement['id'] ?>">Responsable à partir du</label>
                                                    <input type="date" name="date_nomination" id="date_nomination_<?= $rattachement['id'] ?>" value="<?= date('Y-m-d') ?>" required>
                                                    <button type="submit" name="nommer_responsable">Nommer responsable</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php else: ?>
                <p>Aucun rattachement enregistré.</p>
            <?php endif; ?>

            <?php if ($estRH): ?>
                <h2>Identifiants ORCID et IdHAL</h2>
                <form method="post" action="">
                    <input type="hidden" name="id_personnel" value="<?= $personnel['id'] ?>">

                    <label for="id_orcid">ORCID</label>
                    <input type="text" name="id_orcid" id="id_orcid" placeholder="0000-0000-0000-0000" value="<?= htmlspecialchars($personnel['id_orcid'] ?? '') ?>">

                    <label for="id_idhal">IdHAL</label>
                    <input type="text" name="id_idhal" id="id_idhal" value="<?= htmlspecialchars($personnel['id_idhal'] ?? '') ?>">

                    <button type="submit" name="modifier_identifiants">Enregistrer</button>
                </form>

                <h2>Changer le nom d'usage</h2>
                <form method="post" action="">
                    <input type="hidden" name="id_personnel" value="<?= $personnel['id'] ?>">

                    <label for="nouveau_nom">Nouveau nom</label>
                    <input type="text" name="nouveau_nom" id="nouveau_nom" required>

                    <label for="date_changement">Date du changement</label>
                    <input type="date" name="date_changement" id="date_changement" value="<?= date('Y-m-d') ?>" required>

                    <button type="submit" name="changer_nom">Changer le nom</button>
                </form>
            <?php endif; ?>

            <?php if ($personnel['date_depart'] === null && $estRH): ?>
                <h2>Désactiver le personnel</h2>
                <form method="post" action="">
                    <input type="hidden" name="id_personnel" value="<?= $personnel['id'] ?>">

                    <label for="date_depart">Date de départ</label>
                    <input type="date" name="date_depart" id="date_depart" value="<?= date('Y-m-d') ?>" required>

                    <label>
                        <input type="checkbox" name="confirmation" required>
                        Je confirme la désactivation
                    </label>

                    <button type="submit" name="desactiver" value="true">Désactiver le personnel</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <p>
            <a href="afficherListePersonnel.php?tout=1">Retour à la liste</a>
        </p>

    </div>

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