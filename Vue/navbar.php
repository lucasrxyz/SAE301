<nav class="navbar">
    <div class="navbar-logo">
        K.A.B.L Solution <span>Hauts-de-France</span>
    </div>
    <ul class="navbar-liens">
        <li><a href="afficherListePersonnel.php">Personnel</a></li>
        <li><a href="organisation.php">Organisation</a></li>
        <li><a href="declarerActivite.php">Déclarer une activité</a></li>
        <?php if (in_array('Gestionnaire RH', $_SESSION['roles'] ?? []) || in_array('Administrateur fonctionnel', $_SESSION['roles'] ?? [])): ?>
            <li><a href="ajouterpersonnel.php">Ajouter un personnel</a></li>
            <li><a href="creerCompte.php">Créer un compte</a></li>
        <?php endif; ?>
        <li><a href="deconnexion.php">Se déconnecter (<?= htmlspecialchars($_SESSION['identifiant'] ?? '') ?>)</a></li>
    </ul>
</nav>brahim.haddadi@s103pc16:~/Documents/SAE/SAE301$ git status
rebasage interactif en cours ; sur c046823
Dernière commande effectuée (1 commande effectuée) :
   pick 503e6a5 rattachement
Aucune commande restante.
Vous êtes en train de rebaser la branche 'main' sur 'c046823'.
  (réglez les conflits puis lancez "git rebase --continue")
  (utilisez "git rebase --skip" pour sauter ce patch)
  (utilisez "git rebase --abort" pour extraire la branche d'origine)

Modifications qui seront validées :
  (utilisez "git restore --staged <fichier>..." pour désindexer)
        nouveau fichier : Model/ajouterrattachement.php
        nouveau fichier : Vue/ajouterRattachement.php
        modifié :         Vue/ficheDetailleePersonnel.php

Chemins non fusionnés :
  (utilisez "git restore --staged <fichier>..." pour désindexer)
  (utilisez "git add <fichier>..." pour marquer comme résolu)
        modifié des deux côtés :  Vue/navbar.php

brahim.haddadi@s103pc16:~/Documents/SAE/SAE301$ 