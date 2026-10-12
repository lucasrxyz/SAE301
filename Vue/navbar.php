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
?>
<nav class="navbar">
    <div class="navbar-logo">
        K.A.B.L Solution <span>Hauts-de-France</span>
    </div>
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
        <?php endif; ?>
        <?php if (verifierDroit('gerer_personnel')): ?>
            <li><a href="creerCompte.php">Créer un compte</a></li>
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