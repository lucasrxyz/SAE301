<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';

// Réservé à l'administrateur
Droits::exiger('gerer_roles');

// Messages vides au départ
$erreurRole = '';
$messageRole = '';

// Message de réussite selon l'adresse de la page
if (isset($_GET['ajoute'])) {
    $messageRole = 'Le rôle a été attribué.';
}
if (isset($_GET['retire'])) {
    $messageRole = 'Le rôle a été retiré.';
}

// Liste de tous les rôles pour le formulaire
$tousLesRoles = $compteModele->tousLesRoles();

// Le code suivant s'exécute seulement quand un formulaire est envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCompte = $_POST['id_compte'] ?? '';
    $idRole = $_POST['id_role'] ?? '';

    // Bouton « ajouter un rôle »
    if (isset($_POST['ajouter_role'])) {
        if ($idRole === '') {
            $erreurRole = 'Veuillez choisir un rôle.';
        } elseif ($compteModele->aLeRole($idCompte, $idRole)) {
            $erreurRole = 'Ce compte a déjà ce rôle.';
        } else {
            $compteModele->ajouterRole($idCompte, $idRole);
            header('Location: gererRoles.php?ajoute=1');
            exit;
        }
    }

    // Bouton « retirer un rôle »
    if (isset($_POST['retirer_role'])) {
        // Un administrateur ne peut pas retirer son propre rôle d'administrateur
        if ($idCompte == $_SESSION['id_compte'] && $compteModele->nomRole($idRole) === 'Administrateur fonctionnel') {
            $erreurRole = 'Vous ne pouvez pas retirer votre propre rôle d\'administrateur.';
        } else {
            $compteModele->retirerRole($idCompte, $idRole);
            header('Location: gererRoles.php?retire=1');
            exit;
        }
    }
}

// En cas d'erreur, on n'affiche pas de message de réussite
if ($erreurRole !== '') {
    $messageRole = '';
}

// Liste de tous les comptes, et les rôles de chacun
$comptes = $compteModele->tousLesComptes();
$rolesParCompte = $compteModele->rolesParCompte();

require __DIR__ . '/../Vue/gererRoles.php';
