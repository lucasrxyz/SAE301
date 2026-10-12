<?php
require_once __DIR__ . '/verifierconnexion.php';

exigerDroit('gerer_personnel');

$erreurCompte = '';
$compteCree = isset($_GET['cree']);
$idPersonnelChoisi = '';
$identifiantSaisi = '';
$idRoleChoisi = '';

$requete = $pdo->query('SELECT Personnel.id, Personnel.nom, Personnel.prenom, Personnel.mail
    FROM Personnel
    LEFT JOIN Compte ON Compte.id_personnel = Personnel.id
    WHERE Personnel.date_depart IS NULL AND Compte.id_compte IS NULL
    ORDER BY Personnel.nom, Personnel.prenom');
$personnelsSansCompte = $requete->fetchAll();

if (verifierDroit('gerer_roles')) {
    $requete = $pdo->query('SELECT id_role, nom_role FROM Role ORDER BY niveau_pyramidal DESC');
} else {
    $requete = $pdo->query('SELECT id_role, nom_role FROM Role WHERE niveau_pyramidal > 0 ORDER BY niveau_pyramidal DESC');
}
$listeRoles = $requete->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPersonnelChoisi = $_POST['id_personnel'] ?? '';
    $identifiantSaisi = trim($_POST['identifiant'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $idRoleChoisi = $_POST['id_role'] ?? '';

    $requete = $pdo->prepare('SELECT COUNT(*) FROM Compte WHERE identifiant = ?');
    $requete->execute([$identifiantSaisi]);
    $identifiantPris = $requete->fetchColumn() > 0;

    if ($idPersonnelChoisi === '' || $identifiantSaisi === '' || $motDePasse === '' || $idRoleChoisi === '') {
        $erreurCompte = 'Veuillez remplir tous les champs.';
    } elseif (strlen($motDePasse) < 8) {
        $erreurCompte = 'Le mot de passe doit faire au moins 8 caractères.';
    } elseif (!in_array($idPersonnelChoisi, array_column($personnelsSansCompte, 'id'))) {
        $erreurCompte = 'Ce personnel a déjà un compte ou est introuvable.';
    } elseif (!in_array($idRoleChoisi, array_column($listeRoles, 'id_role'))) {
        $erreurCompte = 'Vous ne pouvez pas attribuer ce rôle.';
    } elseif ($identifiantPris) {
        $erreurCompte = 'Cet identifiant est déjà utilisé.';
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