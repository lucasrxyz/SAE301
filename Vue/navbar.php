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
        <li><a href="deconnexion.php">Se déconnecter (<?= htmlspecialchars($_SESSION['identifiant'] ?? '') ?>)</a></li>
    </ul>
</nav>