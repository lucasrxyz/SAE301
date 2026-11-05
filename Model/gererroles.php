<?php
// Connexion à la base de données
require_once __DIR__ . '/config.php';

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
$requete = $pdo->query('SELECT id_role, nom_role, niveau_pyramidal FROM Role ORDER BY niveau_pyramidal, nom_role');
$tousLesRoles = $requete->fetchAll(PDO::FETCH_ASSOC);

// Le code suivant s'exécute seulement quand un formulaire est envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCompte = $_POST['id_compte'] ?? '';
    $idRole = $_POST['id_role'] ?? '';

    // Bouton « ajouter un rôle »
    if (isset($_POST['ajouter_role'])) {
        // Regarde si le compte a déjà ce rôle
        $requete = $pdo->prepare('SELECT COUNT(*) FROM Compte_Role WHERE id_compte = ? AND id_role = ?');
        $requete->execute([$idCompte, $idRole]);

        if ($idRole === '') {
            $erreurRole = 'Veuillez choisir un rôle.';
        } elseif ($requete->fetchColumn() > 0) {
            $erreurRole = 'Ce compte a déjà ce rôle.';
        } else {
            $requete = $pdo->prepare('INSERT INTO Compte_Role (id_compte, id_role) VALUES (?, ?)');
            $requete->execute([$idCompte, $idRole]);
            header('Location: gererRoles.php?ajoute=1');
            exit;
        }
    }

    // Bouton « retirer un rôle »
    if (isset($_POST['retirer_role'])) {
        $requete = $pdo->prepare('SELECT nom_role FROM Role WHERE id_role = ?');
        $requete->execute([$idRole]);
        $nomRole = $requete->fetchColumn();

        // Un administrateur ne peut pas retirer son propre rôle d'administrateur
        if ($idCompte == $_SESSION['id_compte'] && $nomRole === 'Administrateur fonctionnel') {
            $erreurRole = 'Vous ne pouvez pas retirer votre propre rôle d\'administrateur.';
        } else {
            $requete = $pdo->prepare('DELETE FROM Compte_Role WHERE id_compte = ? AND id_role = ?');
            $requete->execute([$idCompte, $idRole]);
            header('Location: gererRoles.php?retire=1');
            exit;
        }
    }
}

// En cas d'erreur, on n'affiche pas de message de réussite
if ($erreurRole !== '') {
    $messageRole = '';
}

// Liste de tous les comptes
$requete = $pdo->query('SELECT Compte.id_compte, Compte.identifiant, Compte.actif, Personnel.nom, Personnel.prenom
    FROM Compte
    LEFT JOIN Personnel ON Personnel.id = Compte.id_personnel
    ORDER BY Personnel.nom, Personnel.prenom');
$comptes = $requete->fetchAll(PDO::FETCH_ASSOC);

// Rôles de chaque compte, rangés par numéro de compte
$requete = $pdo->query('SELECT Compte_Role.id_compte, Role.id_role, Role.nom_role
    FROM Compte_Role
    JOIN Role ON Role.id_role = Compte_Role.id_role
    ORDER BY Role.niveau_pyramidal');
$rolesParCompte = [];
foreach ($requete->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
    $rolesParCompte[$ligne['id_compte']][] = $ligne;
}