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

// Charge la base de données et les droits
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/droits.php';

// Si le compte a été désactivé entre-temps, on déconnecte la personne
$requete = $pdo->prepare('SELECT actif FROM Compte WHERE id_compte = ?');
$requete->execute([$_SESSION['id_compte']]);
if (!$requete->fetchColumn()) {
    header('Location: deconnexion.php');
    exit;
}

// Relit les rôles du compte, pour tenir compte des changements récents
$requete = $pdo->prepare('SELECT Role.nom_role, Role.niveau_pyramidal
    FROM Compte_Role
    JOIN Role ON Role.id_role = Compte_Role.id_role
    WHERE Compte_Role.id_compte = ?
    ORDER BY Role.niveau_pyramidal');
$requete->execute([$_SESSION['id_compte']]);
$rolesCompte = $requete->fetchAll(PDO::FETCH_ASSOC);

// Garde les rôles et le niveau le plus élevé (le plus petit nombre) dans la session
$_SESSION['roles'] = array_column($rolesCompte, 'nom_role');
$_SESSION['niveau'] = $rolesCompte ? $rolesCompte[0]['niveau_pyramidal'] : null;