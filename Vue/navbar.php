<?php
$nomAffiche = trim(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? ''));
if ($nomAffiche === '') {
    $nomAffiche = $_SESSION['identifiant'] ?? '';
}
$grade = $_SESSION['roles'][0] ?? 'Personnel';
?>
<nav class="navbar">
    <div class="navbar-logo">
        K.A.B.L Solution <span>Hauts-de-France</span>
    </div>
    <ul class="navbar-liens">
        <li><a href="tableauDeBord.php">Accueil</a></li>
        <li><a href="afficherListePersonnel.php">Personnel</a></li>
        <li><a href="organisation.php">Organisation</a></li>
        <li><a href="declarerActivite.php">Déclarer une activité</a></li>
        <?php if (in_array('Gestionnaire RH', $_SESSION['roles'] ?? []) || in_array('Administrateur fonctionnel', $_SESSION['roles'] ?? [])): ?>
            <li><a href="creerCompte.php">Créer un compte</a></li>
        <?php endif; ?>
    </ul>
    <div class="navbar-profil">
        <span class="avatar"><?= htmlspecialchars(mb_strtoupper(mb_substr($nomAffiche, 0, 1))) ?></span>
        <div class="profil-texte">
            <strong><?= htmlspecialchars($nomAffiche) ?></strong>
            <small><?= htmlspecialchars($grade) ?></small>
        </div>
        <a href="deconnexion.php" class="lien-deconnexion">Se déconnecter</a>
    </div>
</nav>