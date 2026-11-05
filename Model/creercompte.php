<?php
// Vérifie que l'utilisateur est connecté
require_once __DIR__ . '/verifierconnexion.php';

// Seuls le RH et l'administrateur peuvent créer un compte
exigerDroit('gerer_personnel');

// Valeurs de départ du formulaire
$erreurCompte = '';
$compteCree = isset($_GET['cree']);
$idPersonnelChoisi = '';
$identifiantSaisi = '';
$idRoleChoisi = '';

// Personnels encore présents qui n'ont pas de compte
$requete = $pdo->query('SELECT Personnel.id, Personnel.nom, Personnel.prenom, Personnel.mail
    FROM Personnel
    LEFT JOIN Compte ON Compte.id_personnel = Personnel.id
    WHERE Personnel.date_depart IS NULL AND Compte.id_compte IS NULL
    ORDER BY Personnel.nom, Personnel.prenom');
$personnelsSansCompte = $requete->fetchAll();

// Seul l'administrateur peut donner le rôle d'administrateur
if (verifierDroit('gerer_roles')) {
    $requete = $pdo->query('SELECT id_role, nom_role FROM Role ORDER BY niveau_pyramidal DESC');
} else {
    $requete = $pdo->query('SELECT id_role, nom_role FROM Role WHERE niveau_pyramidal > 0 ORDER BY niveau_pyramidal DESC');
}
$listeRoles = $requete->fetchAll();

// Le code suivant s'exécute seulement quand le formulaire est envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère ce que l'utilisateur a saisi
    $idPersonnelChoisi = $_POST['id_personnel'] ?? '';
    $identifiantSaisi = trim($_POST['identifiant'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $idRoleChoisi = $_POST['id_role'] ?? '';

    // Regarde si cet identifiant est déjà pris
    $requete = $pdo->prepare('SELECT COUNT(*) FROM Compte WHERE identifiant = ?');
    $requete->execute([$identifiantSaisi]);
    $identifiantPris = $requete->fetchColumn() > 0;

    // Contrôle les données : on garde le premier message d'erreur trouvé
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
        // Si une des deux écritures plante, on annule tout
        $pdo->beginTransaction();

        // Crée le compte, avec le mot de passe crypté, et récupère son numéro
        $requete = $pdo->prepare('INSERT INTO Compte (id_personnel, identifiant, mot_de_passe_hash, actif, date_activation) VALUES (?, ?, ?, TRUE, CURRENT_DATE) RETURNING id_compte');
        $requete->execute([$idPersonnelChoisi, $identifiantSaisi, password_hash($motDePasse, PASSWORD_DEFAULT)]);
        $idCompte = $requete->fetchColumn();

        // Donne le rôle choisi au compte
        $requete = $pdo->prepare('INSERT INTO Compte_Role (id_compte, id_role) VALUES (?, ?)');
        $requete->execute([$idCompte, $idRoleChoisi]);

        $pdo->commit();

        // Recharge la page avec le message de réussite
        header('Location: creerCompte.php?cree=1');
        exit;
    }
}