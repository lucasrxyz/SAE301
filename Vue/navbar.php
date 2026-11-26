<nav class="navbar">
    <a href="index.php" class="navbar-logo">
        <img src="Vue/images/logo-uphf.png" alt="Université Polytechnique Hauts-de-France">
    </a>
    <?php // Chaque lien s'affiche seulement si la personne a le droit ?>
    <ul class="navbar-liens">
        <?php if ($peutVoirTableauBord): ?>
            <li><a href="index.php?page=tableauDeBord">Accueil</a></li>
        <?php endif; ?>
        <?php if ($peutVoirPersonnel): ?>
            <li><a href="index.php?page=afficherListePersonnel">Personnel</a></li>
        <?php endif; ?>
        <?php if ($peutVoirOrganisation): ?>
            <li><a href="index.php?page=organisation">Organisation</a></li>
        <?php endif; ?>
        <?php if ($peutDeclarerActivite): ?>
            <li><a href="index.php?page=declarerActivite">Déclarer une activité</a></li>
            <li><a href="index.php?page=mesParticipations">Mes participations<?= $nbInvitations > 0 ? ' (' . $nbInvitations . ')' : '' ?></a></li>
            <li><a href="index.php?page=genererRapport">Générer mon rapport</a></li>
            <li><a href="index.php?page=importerEnseignement">Importer mes enseignements</a></li>
        <?php endif; ?>
        <?php if ($peutGererPersonnel): ?>
            <li><a href="index.php?page=creerCompte">Créer un compte</a></li>
            <li><a href="index.php?page=importerPublications">Importer des publications</a></li>
        <?php endif; ?>
        <?php if ($peutGererRoles): ?>
            <li><a href="index.php?page=gererRoles">Rôles</a></li>
        <?php endif; ?>
    </ul>
    <?php // Profil de la personne connectée et déconnexion ?>
    <div class="navbar-profil">
        <span class="avatar"><?= htmlspecialchars($initiale) ?></span>
        <div class="profil-texte">
            <strong><?= htmlspecialchars($nomAffiche) ?></strong>
            <small><?= htmlspecialchars($grade) ?></small>
        </div>
        <a href="index.php?page=deconnexion" class="lien-deconnexion">Se déconnecter</a>
    </div>
</nav>