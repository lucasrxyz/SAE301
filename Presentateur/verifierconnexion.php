<?php
// Ouvre la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pas connecté : on renvoie vers la page de connexion
if (empty($_SESSION['identifiant'])) {
    header('Location: connexion.php');
    exit;
}

// Charge la base de données et les classes communes à toutes les pages
require_once __DIR__ . '/../Model/config.php';
require_once __DIR__ . '/../Model/Droits.php';
require_once __DIR__ . '/../Model/Compte.php';
require_once __DIR__ . '/../Model/Activite.php';

// Si le compte a été désactivé entre-temps, on déconnecte la personne
$compteModele = new Compte($pdo);
if (!$compteModele->estActif($_SESSION['id_compte'])) {
    header('Location: deconnexion.php');
    exit;
}

// Relit les rôles du compte, pour tenir compte des changements récents
$rolesCompte = $compteModele->roles($_SESSION['id_compte']);

// Garde les rôles et le niveau le plus élevé (le plus petit nombre) dans la session
$_SESSION['roles'] = array_column($rolesCompte, 'nom_role');
$_SESSION['niveau'] = $rolesCompte ? $rolesCompte[0]['niveau_pyramidal'] : null;

// Invitations en attente, affichées dans la barre de navigation
$nbInvitations = 0;
if (Droits::verifier('declarer_activite')) {
    $activiteModele = new Activite($pdo);
    $nbInvitations = $activiteModele->nombreInvitations($_SESSION['id_personnel']);
}

// Ce que la vue a le droit d'afficher : elle ne consulte jamais les droits elle-même
$peutVoirTableauBord = Droits::verifier('voir_tableau_bord');
$peutVoirPersonnel = Droits::verifier('voir_personnel');
$peutVoirOrganisation = Droits::verifier('voir_organisation');
$peutDeclarerActivite = Droits::verifier('declarer_activite');
$peutGererPersonnel = Droits::verifier('gerer_personnel');
$peutGererRoles = Droits::verifier('gerer_roles');
$aucunRole = empty($_SESSION['roles']);
$pageAccueil = Droits::pageAccueil();

// Nom à afficher dans la barre de navigation : prénom et nom, sinon l'identifiant
$nomAffiche = trim(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? ''));
if ($nomAffiche === '') {
    $nomAffiche = $_SESSION['identifiant'] ?? '';
}
// Rôle principal de la personne
$grade = $_SESSION['roles'][0] ?? 'Aucun rôle';
// Première lettre du nom, en majuscule, pour le rond de profil
$initiale = function_exists('mb_substr')
    ? mb_substr($nomAffiche, 0, 1, 'UTF-8')
    : (preg_match('/^./us', $nomAffiche, $premiereLettre) ? $premiereLettre[0] : '');
$initiale = function_exists('mb_strtoupper')
    ? mb_strtoupper($initiale, 'UTF-8')
    : strtoupper($initiale);
