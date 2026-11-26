<nav class="navbar">
    <a href="index.php" class="navbar-logo">
        <img src="../Vue/images/logo-uphf.png" alt="Université Polytechnique Hauts-de-France">
    </a>
    <?php // Chaque lien s'affiche seulement si la personne a le droit ?>
    <ul class="navbar-liens">
        <?php if ($peutVoirTableauBord): ?>
            <li><a href="tableauDeBord.php">Accueil</a></li>
        <?php endif; ?>
        <?php if ($peutVoirPersonnel): ?>
            <li><a href="afficherListePersonnel.php">Personnel</a></li>
        <?php endif; ?>
        <?php if ($peutVoirOrganisation): ?>
            <li><a href="organisation.php">Organisation</a></li>
        <?php endif; ?>
        <?php if ($peutDeclarerActivite): ?>
            <li><a href="declarerActivite.php">Déclarer une activité</a></li>
            <li><a href="mesParticipations.php">Mes participations<?= $nbInvitations > 0 ? ' (' . $nbInvitations . ')' : '' ?></a></li>
            <li><a href="genererRapport.php">Générer mon rapport</a></li>
            <li><a href="importerEnseignement.php">Importer mes enseignements</a></li>
        <?php endif; ?>
        <?php if ($peutGererPersonnel): ?>
            <li><a href="creerCompte.php">Créer un compte</a></li>
            <li><a href="importerPublications.php">Importer des publications</a></li>
        <?php endif; ?>
        <?php if ($peutGererRoles): ?>
            <li><a href="gererRoles.php">Rôles</a></li>
        <?php endif; ?>
    </ul>
    <?php // Profil de la personne connectée et déconnexion ?>
    <div class="navbar-profil">
        <span class="avatar"><?= htmlspecialchars($initiale) ?></span>
        <div class="profil-texte">
            <strong><?= htmlspecialchars($nomAffiche) ?></strong>
            <small><?= htmlspecialchars($grade) ?></small>
        </div>
        <a href="deconnexion.php" class="lien-deconnexion">Se déconnecter</a>
    </div>
</nav>