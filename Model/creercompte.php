<?php
require_once __DIR__ . '/config.php';

$estAutorise = in_array('Gestionnaire RH', $_SESSION['roles'] ?? []) || in_array('Administrateur fonctionnel', $_SESSION['roles'] ?? []);

if (!$estAutorise) {
    header('Location: afficherListePersonnel.php');
    exit;
}

$erreurCompte = '';
$compteCree = isset($_GET['cree']);
$idPersonnelChoisi = '';
$identifiantSaisi = '';
$idRoleChoisi = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPersonnelChoisi = $_POST['id_personnel'] ?? '';
    $identifiantSaisi = trim($_POST['identifiant'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $idRoleChoisi = $_POST['id_role'] ?? '';

    if ($idPersonnelChoisi === '' || $identifiantSaisi === '' || $motDePasse === '' || $idRoleChoisi === '') {
        $erreurCompte = 'Veuillez remplir tous les champs.';
    } elseif (strlen($motDePasse) < 8) {
        $erreurCompte = 'Le mot de passe doit faire au moins 8 caractères.';
    } else {
        $requete = $pdo->prepare('SELECT COUNT(*) FROM Compte WHERE identifiant = ?');
        $requete->execute([$identifiantSaisi]);
        $identifiantPris = $requete->fetchColumn() > 0;

        $requete = $pdo->prepare('SELECT COUNT(*) FROM Compte WHERE id_personnel = ?');
        $requete->execute([$idPersonnelChoisi]);
        $dejaUnCompte = $requete->fetchColumn() > 0;

        if ($identifiantPris) {
            $erreurCompte = 'Cet identifiant est déjà utilisé.';
        } elseif ($dejaUnCompte) {
            $erreurCompte = 'Ce personnel a déjà un compte.';
        } else {
            $pdo->beginTransaction();

            $requete = $pdo->prepare('INSERT INTO Compte (id_personnel, identifiant, mot_de_passe_hash, actif, date_activation) VALUES (?, ?, ?, TRUE, CURRENT_DATE) RETURNING id_compte');
            $requete->execute([$idPersonnelChoisi, $identifiantSaisi, password_hash($motDePasse, PASSWORD_DEFAULT)]);
            $idCompte = $requete->fetchColumn();

            $requete = $pdo->prepare('INSERT INTO Compte_Role (id_compte, id_role) VALUES (?, ?)');
            $requete->execute([$idCompte, $idRoleChoisi]);

            $pdo->commit();

            header('Location: creerCompte.php?cree=1');
            exit;
        }
    }
}

$requete = $pdo->query('SELECT id, nom, prenom, mail FROM Personnel WHERE date_depart IS NULL AND id NOT IN (SELECT id_personnel FROM Compte) ORDER BY nom, prenom');
$personnelsSansCompte = $requete->fetchAll(PDO::FETCH_ASSOC);

$requete = $pdo->query('SELECT id_role, nom_role FROM Role ORDER BY niveau_pyramidal DESC');
$listeRoles = $requete->fetchAll(PDO::FETCH_ASSOC);
