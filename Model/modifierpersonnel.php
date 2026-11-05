<?php
// Vérifie que l'utilisateur est connecté
require_once __DIR__ . '/verifierconnexion.php';

// Seuls le RH et l'administrateur peuvent modifier un personnel
exigerDroit('gerer_personnel');

// Le numéro du personnel vient du formulaire, sinon de l'adresse
$idPersonnel = (int) ($_POST['id_personnel'] ?? $_GET['id'] ?? 0);
$erreurModification = '';
$modificationReussie = isset($_GET['modifie']);

// Cherche le personnel en base
$requete = $pdo->prepare('SELECT id, nom, prenom, mail FROM Personnel WHERE id = ?');
$requete->execute([$idPersonnel]);
$personnel = $requete->fetch();
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

    // Regarde si une autre personne utilise déjà ce mail
    $requete = $pdo->prepare('SELECT COUNT(*) FROM Personnel WHERE LOWER(mail) = LOWER(?) AND id <> ?');
    $requete->execute([$mail, $idPersonnel]);
    $mailUtiliseParUnAutre = $requete->fetchColumn() > 0;

    // Contrôle les données : on garde le premier message d'erreur trouvé
    if ($nom === '' || $prenom === '' || $mail === '') {
        $erreurModification = 'Veuillez remplir tous les champs.';
    } elseif (strlen($nom) > 50 || strlen($prenom) > 50 || strlen($mail) > 60) {
        $erreurModification = 'Un ou plusieurs champs dépassent la longueur autorisée.';
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $erreurModification = 'Veuillez saisir une adresse mail valide.';
    } elseif ($mailUtiliseParUnAutre) {
        $erreurModification = 'Cette adresse mail est déjà utilisée par un autre personnel.';
    } else {
        // Si une écriture plante, on annule tout
        $pdo->beginTransaction();

        // Si le nom change, on garde l'ancien dans l'historique
        if ($nom !== $ancienNom) {
            $requete = $pdo->prepare('INSERT INTO Personnel_Nom_Historique (id_personnel, ancien_nom, date_changement) VALUES (?, ?, CURRENT_DATE)');
            $requete->execute([$idPersonnel, $ancienNom]);
        }

        // Enregistre les nouvelles valeurs
        $requete = $pdo->prepare('UPDATE Personnel SET nom = ?, prenom = ?, mail = ? WHERE id = ?');
        $requete->execute([$nom, $prenom, $mail, $idPersonnel]);

        $pdo->commit();

        // Recharge la page avec le message de réussite
        header('Location: modifierPersonnel.php?id=' . $idPersonnel . '&modifie=1');
        exit;
    }
}