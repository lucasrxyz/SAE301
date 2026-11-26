<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';

// Seuls le RH et l'administrateur peuvent créer un compte
Droits::exiger('gerer_personnel');

// Valeurs de départ du formulaire
$erreurCompte = '';
$compteCree = isset($_GET['cree']);
$idPersonnelChoisi = '';
$identifiantSaisi = '';
$idRoleChoisi = '';

// Personnels encore présents qui n'ont pas de compte
$personnelsSansCompte = $compteModele->personnelsSansCompte();

// Seul l'administrateur peut donner le rôle d'administrateur
$listeRoles = $compteModele->rolesAttribuables(Droits::verifier('gerer_roles'));

// Le code suivant s'exécute seulement quand le formulaire est envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère ce que l'utilisateur a saisi
    $idPersonnelChoisi = $_POST['id_personnel'] ?? '';
    $identifiantSaisi = trim($_POST['identifiant'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $idRoleChoisi = $_POST['id_role'] ?? '';

    // Contrôle les données : on garde le premier message d'erreur trouvé
    if ($idPersonnelChoisi === '' || $identifiantSaisi === '' || $motDePasse === '' || $idRoleChoisi === '') {
        $erreurCompte = 'Veuillez remplir tous les champs.';
    } elseif (strlen($motDePasse) < 8) {
        $erreurCompte = 'Le mot de passe doit faire au moins 8 caractères.';
    } elseif (!in_array($idPersonnelChoisi, array_column($personnelsSansCompte, 'id'))) {
        $erreurCompte = 'Ce personnel a déjà un compte ou est introuvable.';
    } elseif (!in_array($idRoleChoisi, array_column($listeRoles, 'id_role'))) {
        $erreurCompte = 'Vous ne pouvez pas attribuer ce rôle.';
    } elseif ($compteModele->identifiantPris($identifiantSaisi)) {
        $erreurCompte = 'Cet identifiant est déjà utilisé.';
    } else {
        $compteModele->creer($idPersonnelChoisi, $identifiantSaisi, $motDePasse, $idRoleChoisi);

        // Recharge la page avec le message de réussite
        header('Location: index.php?page=creerCompte&cree=1');
        exit;
    }
}

require __DIR__ . '/../Vue/creerCompte.php';
