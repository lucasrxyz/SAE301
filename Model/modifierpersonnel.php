<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['identifiant'])) {
    header('Location: ../Vue/connexion.php');
    exit;
}

$estAutorise = in_array('Gestionnaire RH', $_SESSION['roles'] ?? []) || in_array('Administrateur fonctionnel', $_SESSION['roles'] ?? []);

if (!$estAutorise) {
    header('Location: ../Vue/afficherListePersonnel.php');
    exit;
}

require_once __DIR__ . '/config.php';

$idPersonnel = $_POST['id_personnel'] ?? $_GET['id'] ?? '';
$personnel = null;
$personnelIntrouvable = false;
$erreurModification = '';
$modificationReussie = isset($_GET['modifie']);

if (filter_var($idPersonnel, FILTER_VALIDATE_INT) !== false && (int) $idPersonnel > 0) {
    $requete = $pdo->prepare('SELECT id, nom, prenom, mail FROM Personnel WHERE id = ?');
    $requete->execute([$idPersonnel]);
    $personnel = $requete->fetch(PDO::FETCH_ASSOC);
}

if (!$personnel) {
    $personnelIntrouvable = true;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $mail = trim($_POST['mail'] ?? '');

    $personnel['nom'] = $nom;
    $personnel['prenom'] = $prenom;
    $personnel['mail'] = $mail;

    if ($nom === '' || $prenom === '' || $mail === '') {
        $erreurModification = 'Veuillez remplir tous les champs.';
    } elseif (strlen($nom) > 50 || strlen($prenom) > 50 || strlen($mail) > 60) {
        $erreurModification = 'Un ou plusieurs champs dépassent la longueur autorisée.';
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $erreurModification = 'Veuillez saisir une adresse mail valide.';
    } else {
        $requete = $pdo->prepare('SELECT COUNT(*) FROM Personnel WHERE LOWER(mail) = LOWER(?) AND id <> ?');
        $requete->execute([$mail, $idPersonnel]);
        $mailUtiliseParUnAutre = $requete->fetchColumn() > 0;

        if ($mailUtiliseParUnAutre) {
            $erreurModification = 'Cette adresse mail est déjà utilisée par un autre personnel.';
        } else {
            $requete = $pdo->prepare('UPDATE Personnel SET nom = ?, prenom = ?, mail = ? WHERE id = ?');
            $requete->execute([$nom, $prenom, $mail, $idPersonnel]);

            header('Location: modifierPersonnel.php?id=' . urlencode((string) $idPersonnel) . '&modifie=1');
            exit;
        }
    }
}