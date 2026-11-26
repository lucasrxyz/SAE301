<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';
require_once __DIR__ . '/../Model/Personnel.php';

// Seuls le RH et l'administrateur peuvent modifier un personnel
Droits::exiger('gerer_personnel');

$personnelModele = new Personnel($pdo);

// Le numéro du personnel vient du formulaire, sinon de l'adresse
$idPersonnel = (int) ($_POST['id_personnel'] ?? $_GET['id'] ?? 0);
$erreurModification = '';
$modificationReussie = isset($_GET['modifie']);

// Cherche le personnel en base
$personnel = $personnelModele->trouver($idPersonnel);
$personnelIntrouvable = !$personnel;

// Le code suivant s'exécute seulement quand le formulaire est envoyé
if ($personnel && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Garde l'ancien nom et récupère ce qui a été saisi
    $ancienNom = $personnel['nom'];
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $mail = trim($_POST['mail'] ?? '');

    // Garde la saisie pour la réafficher en cas d'erreur
    $personnel['nom'] = $nom;
    $personnel['prenom'] = $prenom;
    $personnel['mail'] = $mail;

    // Contrôle les données : on garde le premier message d'erreur trouvé
    if ($nom === '' || $prenom === '' || $mail === '') {
        $erreurModification = 'Veuillez remplir tous les champs.';
    } elseif (strlen($nom) > 50 || strlen($prenom) > 50 || strlen($mail) > 60) {
        $erreurModification = 'Un ou plusieurs champs dépassent la longueur autorisée.';
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $erreurModification = 'Veuillez saisir une adresse mail valide.';
    } elseif ($personnelModele->mailUtilise($mail, $idPersonnel)) {
        $erreurModification = 'Cette adresse mail est déjà utilisée par un autre personnel.';
    } else {
        $personnelModele->modifier($idPersonnel, $ancienNom, $nom, $prenom, $mail);

        // Recharge la page avec le message de réussite
        header('Location: modifierPersonnel.php?id=' . $idPersonnel . '&modifie=1');
        exit;
    }
}

require __DIR__ . '/../Vue/modifierPersonnel.php';
