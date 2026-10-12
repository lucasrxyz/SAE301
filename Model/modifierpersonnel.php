<?php
require_once __DIR__ . '/verifierconnexion.php';

exigerDroit('gerer_personnel');

$idPersonnel = (int) ($_POST['id_personnel'] ?? $_GET['id'] ?? 0);
$erreurModification = '';
$modificationReussie = isset($_GET['modifie']);

$requete = $pdo->prepare('SELECT id, nom, prenom, mail FROM Personnel WHERE id = ?');
$requete->execute([$idPersonnel]);
$personnel = $requete->fetch();
$personnelIntrouvable = !$personnel;

if ($personnel && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ancienNom = $personnel['nom'];
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $mail = trim($_POST['mail'] ?? '');

    $personnel['nom'] = $nom;
    $personnel['prenom'] = $prenom;
    $personnel['mail'] = $mail;

    $requete = $pdo->prepare('SELECT COUNT(*) FROM Personnel WHERE LOWER(mail) = LOWER(?) AND id <> ?');
    $requete->execute([$mail, $idPersonnel]);
    $mailUtiliseParUnAutre = $requete->fetchColumn() > 0;

    if ($nom === '' || $prenom === '' || $mail === '') {
        $erreurModification = 'Veuillez remplir tous les champs.';
    } elseif (strlen($nom) > 50 || strlen($prenom) > 50 || strlen($mail) > 60) {
        $erreurModification = 'Un ou plusieurs champs dépassent la longueur autorisée.';
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $erreurModification = 'Veuillez saisir une adresse mail valide.';
    } elseif ($mailUtiliseParUnAutre) {
        $erreurModification = 'Cette adresse mail est déjà utilisée par un autre personnel.';
    } else {
        $pdo->beginTransaction();

        if ($nom !== $ancienNom) {
            $requete = $pdo->prepare('INSERT INTO Personnel_Nom_Historique (id_personnel, ancien_nom, date_changement) VALUES (?, ?, CURRENT_DATE)');
            $requete->execute([$idPersonnel, $ancienNom]);
        }

        $requete = $pdo->prepare('UPDATE Personnel SET nom = ?, prenom = ?, mail = ? WHERE id = ?');
        $requete->execute([$nom, $prenom, $mail, $idPersonnel]);

        $pdo->commit();

        header('Location: modifierPersonnel.php?id=' . $idPersonnel . '&modifie=1');
        exit;
    }
}