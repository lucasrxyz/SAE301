<?php
$nomAffiche = trim(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? ''));
if ($nomAffiche === '') {
    $nomAffiche = $_SESSION['identifiant'] ?? '';
}
$grade = $_SESSION['roles'][0] ?? 'Aucun rôle';
$initiale = function_exists('mb_substr')
    ? mb_substr($nomAffiche, 0, 1, 'UTF-8')
    : (preg_match('/^./us', $nomAffiche, $premiereLettre) ? $premiereLettre[0] : '');
$initiale = function_exists('mb_strtoupper')
    ? mb_strtoupper($initiale, 'UTF-8')
    : strtoupper($initiale);

$nbInvitations = 0;
if (verifierDroit('declarer_activite')) {
    $requete = $pdo->prepare('SELECT COUNT(*) FROM Activite WHERE id_personnel = ? AND id_activite_principale IS NOT NULL AND confirme IS NULL');
    $requete->execute([$_SESSION['id_personnel']]);
    $nbInvitations = $requete->fetchColumn();
}
?>
<nav class="navbar">
    <a href="index.php" class="navbar-logo">
        <img src="images/logo-uphf.png" alt="Université Polytechnique Hauts-de-France">
    </a>
    <ul class="navbar-liens">
        <?php if (verifierDroit('voir_tableau_bord')): ?>
            <li><a href="tableauDeBord.php">Accueil</a></li>
        <?php endif; ?>
        <?php if (verifierDroit('voir_personnel')): ?>
            <li><a href="afficherListePersonnel.php">Personnel</a></li>
        <?php endif; ?>
        <?php if (verifierDroit('voir_organisation')): ?>
            <li><a href="organisation.php">Organisation</a></li>
        <?php endif; ?>
        <?php if (verifierDroit('declarer_activite')): ?>
            <li><a href="declarerActivite.php">Déclarer une activité</a></li>
            <li><a href="mesParticipations.php">Mes participations<?= $nbInvitations > 0 ? ' (' . $nbInvitations . ')' : '' ?></a></li>
        <?php endif; ?>
        <?php if (verifierDroit('gerer_personnel')): ?>
            <li><a href="creerCompte.php">Créer un compte</a></li>
            <li><a href="importerPublications.php">Importer des publications</a></li>
            <li><a href="importerEnseignement.php">Importer un enseignement</a></li>
        <?php endif; ?>
        <?php if (verifierDroit('gerer_roles')): ?>
            <li><a href="gererRoles.php">Rôles</a></li>
        <?php endif; ?>
    </ul>
    <div class="navbar-profil">
        <span class="avatar"><?= htmlspecialchars($initiale) ?></span>
        <div class="profil-texte">
            <strong><?= htmlspecialchars($nomAffiche) ?></strong>
            <small><?= htmlspecialchars($grade) ?></small>
        </div>
        <a href="deconnexion.php" class="lien-deconnexion">Se déconnecter</a>
    </div>
</nav>